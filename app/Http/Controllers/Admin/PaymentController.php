<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PaymentConfirmed;
use App\Mail\PaymentRejected as PaymentRejectedMail;
use App\Models\Apartment;
use App\Models\BankAccount;
use App\Models\Condominium;
use App\Models\MonthlyBill;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PaymentController extends Controller
{
    private AuditLogService $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }

    public function index(): View
    {
        $user = Auth::user();

        $payments = Payment::with('apartment', 'user', 'bankAccount', 'bill')
            ->when($user->role === 'admin', fn($q) => $q->where('condominium_id', $user->condominium_id))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.payments.index', compact('payments'));
    }

    public function create(): View
    {
        $condominiums = $this->getCondominiumsForSelect();
        $apartments = $this->getApartmentsForSelect();
        $bankAccounts = $this->getBankAccountsForSelect();

        return view('admin.payments.create', compact('condominiums', 'apartments', 'bankAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'condominium_id' => 'required|exists:condominiums,id',
            'apartment_id' => 'required|exists:apartments,id',
            'user_id' => 'nullable|exists:users,id',
            'bill_id' => 'nullable|exists:monthly_bills,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'voucher_path' => 'nullable|string|max:500',
        ]);

        $this->authorizeCondo($validated['condominium_id']);

        $validated['status'] = 'pending';

        Payment::create($validated);

        return redirect()->route('payments.index')
            ->with('success', __('messages.common.save') . '!');
    }

    public function show(Payment $payment): View
    {
        $this->authorizeCondo($payment->condominium_id);

        $payment->load('apartment', 'user', 'bankAccount', 'bill', 'confirmer');

        return view('admin.payments.show', compact('payment'));
    }

    public function edit(Payment $payment): View
    {
        $this->authorizeCondo($payment->condominium_id);

        $condominiums = $this->getCondominiumsForSelect();
        $apartments = $this->getApartmentsForSelect();
        $bankAccounts = $this->getBankAccountsForSelect();

        return view('admin.payments.edit', compact('payment', 'condominiums', 'apartments', 'bankAccounts'));
    }

    public function update(Request $request, Payment $payment)
    {
        $this->authorizeCondo($payment->condominium_id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'voucher_path' => 'nullable|string|max:500',
        ]);

        $payment->update($validated);

        return redirect()->route('payments.show', $payment)
            ->with('success', __('messages.common.save') . '!');
    }

    public function destroy(Payment $payment)
    {
        $this->authorizeCondo($payment->condominium_id);

        $payment->delete();

        return redirect()->route('payments.index')
            ->with('success', __('messages.common.delete') . '!');
    }

    public function confirm(Payment $payment)
    {
        $this->authorizeCondo($payment->condominium_id);

        if ($payment->status !== 'pending') {
            return redirect()->route('payments.show', $payment)
                ->with('error', __('messages.common.error_message'));
        }

        $payment->update([
            'status' => 'confirmed',
            'confirmed_by' => Auth::id(),
            'confirmed_at' => now(),
        ]);

        $this->auditLog->log('payment_confirmed', 'payments', $payment->id, null, ['status' => 'confirmed', 'confirmed_by' => Auth::id()]);

        if ($payment->bill_id) {
            $bill = MonthlyBill::find($payment->bill_id);
            if ($bill) {
                $bill->payments_applied = ($bill->payments_applied ?? 0) + $payment->amount;

                if ($bill->payments_applied >= $bill->total) {
                    $bill->status = 'paid';
                } elseif ($bill->payments_applied > 0) {
                    $bill->status = 'partial';
                }

                $bill->save();
            }
        }

        $apartment = Apartment::find($payment->apartment_id);
        if ($apartment) {
            $apartment->decrement('balance', $payment->amount);
        }

        $payment->load('apartment', 'condominium', 'user');
        if ($payment->user && $payment->user->email) {
            try {
                Mail::to($payment->user->email)->send(new PaymentConfirmed($payment));
            } catch (\Throwable $e) {
                \Log::warning('Failed to send payment confirmation email: ' . $e->getMessage());
            }
        }

        return redirect()->route('payments.show', $payment)
            ->with('success', __('messages.common.save') . '!');
    }

    public function reject(Request $request, Payment $payment)
    {
        $this->authorizeCondo($payment->condominium_id);

        if ($payment->status !== 'pending') {
            return redirect()->route('payments.show', $payment)
                ->with('error', __('messages.common.error_message'));
        }

        $validated = $request->validate([
            'admin_observation' => 'nullable|string',
        ]);

        $payment->update([
            'status' => 'rejected',
            'admin_observation' => $validated['admin_observation'] ?? null,
        ]);

        $this->auditLog->log('payment_rejected', 'payments', $payment->id, null, ['status' => 'rejected', 'observation' => $request->admin_observation]);

        $payment->load('apartment', 'condominium', 'user');
        if ($payment->user && $payment->user->email) {
            try {
                Mail::to($payment->user->email)->send(new PaymentRejectedMail($payment));
            } catch (\Throwable $e) {
                \Log::warning('Failed to send payment rejection email: ' . $e->getMessage());
            }
        }

        return redirect()->route('payments.show', $payment)
            ->with('success', __('messages.common.save') . '!');
    }

    private function authorizeCondo(int $condominiumId): void
    {
        $user = Auth::user();
        if ($user->role === 'admin' && $condominiumId !== $user->condominium_id) {
            abort(403, __('messages.auth.unauthorized'));
        }
    }

    private function getCondominiumsForSelect()
    {
        $user = Auth::user();
        if ($user->role === 'super_admin') {
            return Condominium::orderBy('name')->pluck('name', 'id');
        }
        return Condominium::where('id', $user->condominium_id)->pluck('name', 'id');
    }

    private function getApartmentsForSelect()
    {
        $user = Auth::user();
        if ($user->role === 'super_admin') {
            return Apartment::with('condominium')->orderBy('number')->get()->mapWithKeys(fn($a) => [$a->id => $a->condominium->name . ' - ' . $a->number]);
        }
        return Apartment::where('condominium_id', $user->condominium_id)->orderBy('number')->pluck('number', 'id');
    }

    private function getBankAccountsForSelect()
    {
        $user = Auth::user();
        if ($user->role === 'super_admin') {
            return BankAccount::with('condominium')->orderBy('bank_name')->get()->mapWithKeys(fn($b) => [$b->id => $b->condominium->name . ' - ' . $b->bank_name]);
        }
        return BankAccount::where('condominium_id', $user->condominium_id)->orderBy('bank_name')->pluck('bank_name', 'id');
    }
}