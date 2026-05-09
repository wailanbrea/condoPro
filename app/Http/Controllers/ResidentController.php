<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\FinancialMovement;
use App\Models\GasReading;
use App\Models\MonthlyBill;
use App\Models\Payment;
use App\Models\Announcement;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ResidentController extends Controller
{
    private function getApartment()
    {
        $user = Auth::user();

        if ($user->role === 'super_admin' || $user->role === 'admin') {
            return Apartment::where('status', 'active')
                ->orderBy('number')
                ->first();
        }

        return $user->apartments()->first();
    }

    public function index(): View
    {
        $user = Auth::user();

        $apartment = $this->getApartment();

        $latestBill = null;
        $billItems = [];
        $gasReading = null;
        $paymentHistory = [];
        $totalOwed = 0;
        $totalPaid = 0;
        $pendingPayments = 0;

        if ($apartment) {
            $latestBill = MonthlyBill::where('apartment_id', $apartment->id)
                ->orderBy('billing_year', 'desc')
                ->orderBy('billing_month', 'desc')
                ->first();

            if ($latestBill) {
                $items = $latestBill->billItems;

                $billItems = $items->map(function ($item) {
                    $iconMap = [
                        'maintenance' => 'home',
                        'gas' => 'local_gas_station',
                        'extra' => 'add_card',
                    ];
                    return [
                        'icon' => $iconMap[$item->concept_type] ?? 'receipt',
                        'concept' => $item->description,
                        'amount' => $item->amount,
                    ];
                })->toArray();
            }

            $gasReading = GasReading::where('apartment_id', $apartment->id)
                ->orderBy('reading_date_end', 'desc')
                ->first();

            $payments = Payment::where('apartment_id', $apartment->id)
                ->orderBy('payment_date', 'desc')
                ->limit(5)
                ->get();

            $paymentHistory = $payments->map(function ($payment) {
                return [
                    'date' => $payment->payment_date->format('d M, Y'),
                    'concept' => $payment->bill ? $payment->bill->billItems->first()?->description ?? __('messages.common.concept') : __('messages.common.concept'),
                    'amount' => $payment->amount,
                    'status' => $payment->status,
                    'reference' => $payment->reference_number ?? str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT),
                ];
            })->toArray();

            $totalOwed = MonthlyBill::where('apartment_id', $apartment->id)
                ->whereIn('status', ['pending', 'partial', 'overdue'])
                ->selectRaw('COALESCE(SUM(total - payments_applied), 0) as owed')
                ->value('owed');

            $totalPaid = MonthlyBill::where('apartment_id', $apartment->id)
                ->whereIn('status', ['pending', 'partial', 'overdue'])
                ->sum('payments_applied');

            $totalPaid += Payment::where('apartment_id', $apartment->id)
                ->where('status', 'confirmed')
                ->whereNull('bill_id')
                ->sum('amount');

            $pendingPayments = Payment::where('apartment_id', $apartment->id)
                ->where('status', 'pending')
                ->sum('amount');
        }

        // Fondo del Condominio (global: all-time cobrado - gastos + ajustes)
        $condoFund = 0;
        $condominiumId = $apartment?->condominium_id ?? $user->condominium_id;
        if ($condominiumId) {
            $allTimeCollected = Payment::where('condominium_id', $condominiumId)
                ->where('status', 'confirmed')
                ->sum('amount');
            $allTimeExpenses = Expense::where('condominium_id', $condominiumId)
                ->sum('amount');
            $allTimeAdjustments = FinancialMovement::where('condominium_id', $condominiumId)
                ->where('movement_type', 'adjustment')
                ->sum('amount');
            $condoFund = $allTimeCollected - $allTimeExpenses + $allTimeAdjustments;
        }

        // Get recent notifications for the dashboard
        $recentNotifications = Notification::forUser($user->id)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        // Get recent announcements
        $recentAnnouncements = collect();
        if ($condominiumId) {
            $recentAnnouncements = Announcement::where('condominium_id', $condominiumId)
                ->published()
                ->orderBy('is_pinned', 'desc')
                ->orderBy('published_at', 'desc')
                ->take(2)
                ->get();
        }

        // Get gas history for last 3 months
        $gasHistory = collect();
        if ($apartment) {
            $gasHistory = GasReading::where('apartment_id', $apartment->id)
                ->orderBy('billing_year', 'desc')
                ->orderBy('billing_month', 'desc')
                ->take(3)
                ->get()
                ->map(function($reading) {
                    return [
                        'month' => $reading->billing_month,
                        'year' => $reading->billing_year,
                        'month_name' => \Carbon\Carbon::create($reading->billing_year, $reading->billing_month, 1)->locale(app()->getLocale())->monthName,
                        'consumption' => $reading->consumption_m3,
                        'amount' => $reading->total_amount,
                    ];
                });
        }

        // Calculate gas trend
        $gasTrend = 'stable';
        $gasTrendPercent = 0;
        if ($gasHistory->count() >= 2) {
            $current = $gasHistory->first()['consumption'] ?? 0;
            $previous = $gasHistory->skip(1)->first()['consumption'] ?? 0;
            if ($previous > 0) {
                $gasTrendPercent = round((($current - $previous) / $previous) * 100, 1);
                if ($gasTrendPercent > 5) {
                    $gasTrend = 'up';
                } elseif ($gasTrendPercent < -5) {
                    $gasTrend = 'down';
                }
            }
        }

        // Get financial summary
        $financialSummary = [
            'total_owed' => $totalOwed ?? 0,
            'total_paid' => $totalPaid ?? 0,
            'last_payment' => null,
            'last_payment_date' => null,
            'next_due_date' => $latestBill?->due_date,
            'days_until_due' => $latestBill?->due_date ? now()->diffInDays($latestBill->due_date, false) : null,
        ];

        // Get next pending payment (next bill to pay)
        $nextPayment = null;
        if ($apartment) {
            $nextPayment = MonthlyBill::where('apartment_id', $apartment->id)
                ->whereIn('status', ['pending', 'partial', 'overdue'])
                ->orderBy('due_date', 'asc')
                ->first();
                
            $lastPayment = Payment::where('apartment_id', $apartment->id)
                ->where('status', 'confirmed')
                ->orderBy('payment_date', 'desc')
                ->first();
            if ($lastPayment) {
                $financialSummary['last_payment'] = $lastPayment->amount;
                $financialSummary['last_payment_date'] = $lastPayment->payment_date;
            }
        }

        return view('resident.index', compact(
            'user',
            'apartment',
            'latestBill',
            'billItems',
            'gasReading',
            'paymentHistory',
            'totalOwed',
            'totalPaid',
            'pendingPayments',
            'condoFund',
            'recentNotifications',
            'recentAnnouncements',
            'gasHistory',
            'gasTrend',
            'gasTrendPercent',
            'financialSummary',
            'nextPayment'
        ));
    }

    public function uploadVoucher(): View
    {
        $user = Auth::user();
        $apartment = $this->getApartment();

        $bills = collect();
        $bankAccounts = collect();

        if ($apartment) {
            $bills = MonthlyBill::where('apartment_id', $apartment->id)
                ->whereIn('status', ['pending', 'partial'])
                ->with('billItems')
                ->orderBy('billing_year', 'desc')
                ->orderBy('billing_month', 'desc')
                ->get();

            $bankAccounts = BankAccount::where('condominium_id', $apartment->condominium_id)
                ->where('status', 'active')
                ->get();
        }

        return view('resident.vouchers.upload', compact('bills', 'bankAccounts'));
    }

    public function storeVoucher(Request $request)
    {
        $user = Auth::user();
        $apartment = $this->getApartment();

        if (!$apartment) {
            return redirect()->back()->with('error', __('messages.common.error_message'));
        }

        $validated = $request->validate([
            'bill_ids' => 'nullable|array',
            'bill_ids.*' => 'exists:monthly_bills,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'voucher_path' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $billIds = $validated['bill_ids'] ?? [];
        $validBillIds = [];
        foreach ($billIds as $billId) {
            $bill = MonthlyBill::find($billId);
            if ($bill && $bill->apartment_id === $apartment->id && in_array($bill->status, ['pending', 'partial'])) {
                $validBillIds[] = $billId;
            }
        }

        $firstBillId = count($validBillIds) > 0 ? $validBillIds[0] : null;

        $voucherPath = null;
        if ($request->hasFile('voucher_path')) {
            $voucherPath = $request->file('voucher_path')->store('vouchers', 'public');
        }

        $payment = Payment::create([
            'condominium_id' => $apartment->condominium_id,
            'apartment_id' => $apartment->id,
            'user_id' => $user->id,
            'bill_id' => $firstBillId,
            'bank_account_id' => $validated['bank_account_id'] ?? null,
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'reference_number' => $validated['reference_number'] ?? null,
            'voucher_path' => $voucherPath,
            'status' => 'pending',
        ]);

        if (count($validBillIds) > 0) {
            $payment->bills()->sync([]);
            $totalSelected = 0;
            foreach ($validBillIds as $billId) {
                $bill = MonthlyBill::find($billId);
                $remaining = max(0, $bill->total - $bill->payments_applied);
                $payment->bills()->attach($billId, ['amount' => min($remaining, $validated['amount'] - $totalSelected)]);
                $totalSelected += $remaining;
            }
        }

        return redirect()->route('resident.vouchers.success');
    }

    public function invoices(Request $request): View
    {
        $user = Auth::user();
        $apartment = $this->getApartment();

        $bills = collect();
        $years = collect();

        if ($apartment) {
            $years = MonthlyBill::where('apartment_id', $apartment->id)
                ->select('billing_year')
                ->distinct()
                ->orderBy('billing_year', 'desc')
                ->pluck('billing_year');

            $query = MonthlyBill::where('apartment_id', $apartment->id)
                ->with('billItems');

            if ($request->filled('year')) {
                $query->where('billing_year', $request->year);
            }

            $bills = $query->orderBy('billing_year', 'desc')
                ->orderBy('billing_month', 'desc')
                ->get();
        }

        return view('resident.invoices', compact('bills', 'years'));
    }

    public function invoiceDetail(MonthlyBill $bill): View
    {
        $user = Auth::user();
        $apartment = $this->getApartment();

        if (!$apartment) {
            abort(403, __('messages.auth.unauthorized'));
        }

        if ($user->role === 'super_admin' || $user->role === 'admin') {
            // Admin preview: allow access to any bill
        } elseif ($bill->apartment_id !== $apartment->id) {
            abort(403, __('messages.auth.unauthorized'));
        }

        $bill->load('billItems', 'payments');

        return view('resident.invoices-show', compact('bill', 'apartment'));
    }

    public function history(): View
    {
        $user = Auth::user();
        $apartment = $this->getApartment();

        $payments = collect();

        if ($apartment) {
            $payments = Payment::where('apartment_id', $apartment->id)
                ->with('bill.billItems')
                ->orderBy('payment_date', 'desc')
                ->get();
        }

        return view('resident.history', compact('payments'));
    }

    public function voucherSuccess(): View
    {
        return view('resident.vouchers.success');
    }

    public function announcements(): View
    {
        $user = Auth::user();
        $apartment = $this->getApartment();

        $announcements = collect();
        if ($apartment) {
            $announcements = \App\Models\Announcement::where('condominium_id', $apartment->condominium_id)
                ->published()
                ->orderBy('is_pinned', 'desc')
                ->orderBy('published_at', 'desc')
                ->paginate(10);
        }

        return view('resident.announcements', compact('announcements', 'apartment'));
    }
}