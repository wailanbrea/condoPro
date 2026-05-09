<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\BillItem;
use App\Models\Condominium;
use App\Models\ExtraCharge;
use App\Models\ExtraChargeInstallment;
use App\Models\GasReading;
use App\Models\MonthlyBill;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BillingController extends Controller
{
    private AuditLogService $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }

    public function index(): View
    {
        $user = Auth::user();

        $bills = MonthlyBill::with('apartment', 'condominium', 'billItems')
            ->when($user->role === 'admin', fn($q) => $q->where('condominium_id', $user->condominium_id))
            ->orderBy('billing_year', 'desc')
            ->orderBy('billing_month', 'desc')
            ->get();

        return view('admin.billing.index', compact('bills'));
    }

    public function create(): View
    {
        $condominiums = $this->getCondominiumsForSelect();
        $apartments = $this->getApartmentsForSelect();

        return view('admin.billing.create', compact('condominiums', 'apartments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'condominium_id' => 'required|exists:condominiums,id',
            'apartment_id' => 'required|exists:apartments,id',
            'billing_month' => 'required|integer|min:1|max:12',
            'billing_year' => 'required|integer|min:2020',
            'due_date' => 'required|date',
        ]);

        $this->authorizeCondo($validated['condominium_id']);

        $exists = MonthlyBill::where('apartment_id', $validated['apartment_id'])
            ->where('billing_month', $validated['billing_month'])
            ->where('billing_year', $validated['billing_year'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['billing_month' => __('messages.billing.already_exists')])->withInput();
        }

        $apartment = Apartment::findOrFail($validated['apartment_id']);

        DB::transaction(function () use ($validated, $apartment) {
            $subtotal = 0;

            $bill = MonthlyBill::create([
                'condominium_id' => $validated['condominium_id'],
                'apartment_id' => $validated['apartment_id'],
                'billing_month' => $validated['billing_month'],
                'billing_year' => $validated['billing_year'],
                'subtotal' => 0,
                'previous_balance' => $apartment->balance,
                'payments_applied' => 0,
                'total' => 0,
                'due_date' => $validated['due_date'],
                'status' => 'pending',
            ]);

            $maintenanceAmount = $apartment->maintenance_fee;
            if ($maintenanceAmount > 0) {
                BillItem::create([
                    'bill_id' => $bill->id,
                    'concept_type' => 'maintenance',
                    'description' => __('messages.billing.concept_maintenance'),
                    'amount' => $maintenanceAmount,
                ]);
                $subtotal += $maintenanceAmount;
            }

            $gasReading = GasReading::where('apartment_id', $apartment->id)
                ->where('billed', false)
                ->orderBy('reading_date_end', 'desc')
                ->first();

            if ($gasReading && $apartment->has_gas_meter && $gasReading->total_amount > 0) {
                $gasDescription = sprintf(
                    'Gas: %s m³ × %s = %s gal × RD$%s = RD$%s',
                    number_format($gasReading->consumption_m3, 3),
                    number_format($gasReading->conversion_factor, 4),
                    number_format($gasReading->gallons, 2),
                    number_format($gasReading->total_gallon_price, 2),
                    number_format($gasReading->total_amount, 2)
                );
                BillItem::create([
                    'bill_id' => $bill->id,
                    'concept_type' => 'gas',
                    'description' => $gasDescription,
                    'amount' => $gasReading->total_amount,
                    'reference_id' => $gasReading->id,
                ]);
                $subtotal += $gasReading->total_amount;
                $gasReading->update(['billed' => true]);
            }

            $installments = ExtraChargeInstallment::where('apartment_id', $apartment->id)
                ->where('billing_month', $validated['billing_month'])
                ->where('billing_year', $validated['billing_year'])
                ->where('status', 'pending')
                ->get();

            foreach ($installments as $installment) {
                $billItem = BillItem::create([
                    'bill_id' => $bill->id,
                    'concept_type' => 'extra_charge',
                    'description' => $installment->extraCharge->title ?? __('messages.billing.concept_extra_charge'),
                    'amount' => $installment->amount,
                    'reference_id' => $installment->extra_charge_id,
                ]);
                $subtotal += $installment->amount;
                $installment->update(['status' => 'billed', 'bill_item_id' => $billItem->id]);
            }

            $bill->update([
                'subtotal' => $subtotal,
                'total' => $apartment->balance + $subtotal,
            ]);

            $this->auditLog->log('bill_created', 'billing', $bill->id);
        });

        return redirect()->route('billing.index')
            ->with('success', __('messages.common.save') . '!');
    }

    public function show(MonthlyBill $billing): View
    {
        $this->authorizeCondo($billing->condominium_id);

        $billing->load('apartment', 'condominium', 'billItems', 'payments');

        return view('admin.billing.show', compact('billing'));
    }

    public function edit(MonthlyBill $billing): View
    {
        $this->authorizeCondo($billing->condominium_id);

        $condominiums = $this->getCondominiumsForSelect();
        $apartments = $this->getApartmentsForSelect();

        return view('admin.billing.edit', compact('billing', 'condominiums', 'apartments'));
    }

    public function update(Request $request, MonthlyBill $billing)
    {
        $this->authorizeCondo($billing->condominium_id);

        $validated = $request->validate([
            'due_date' => 'required|date',
            'status' => 'required|in:pending,partial,paid,overdue,cancelled',
        ]);

        $billing->update($validated);

        return redirect()->route('billing.show', $billing)
            ->with('success', __('messages.common.save') . '!');
    }

    public function destroy(MonthlyBill $billing)
    {
        $this->authorizeCondo($billing->condominium_id);

        $billing->billItems()->delete();
        $billing->delete();

        return redirect()->route('billing.index')
            ->with('success', __('messages.common.delete') . '!');
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
        return Apartment::where('condominium_id', $user->condominium_id)
            ->orderBy('number')
            ->pluck('number', 'id');
    }
}