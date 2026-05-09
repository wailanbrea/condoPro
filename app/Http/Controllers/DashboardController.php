<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Condominium;
use App\Models\Expense;
use App\Models\FinancialMovement;
use App\Models\MonthlyBill;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $condominiumId = $user->condominium_id;

        if ($user->role === 'super_admin' && !$condominiumId) {
            $firstCondo = Condominium::first();
            $condominiumId = $firstCondo?->id;
        }

        $month = (int) ($request->input('month', now()->month));
        $year = (int) ($request->input('year', now()->year));

        if ($month < 1) $month = 1;
        if ($month > 12) $month = 12;

        $date = now()->setDate($year, $month, 1);

        // Total Facturado (billed this period)
        $totalBilled = MonthlyBill::where('condominium_id', $condominiumId)
            ->where('billing_month', $month)
            ->where('billing_year', $year)
            ->sum('total');

        // Total Cobrado (confirmed payments this period)
        $totalCollected = Payment::where('condominium_id', $condominiumId)
            ->where('status', 'confirmed')
            ->whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year)
            ->sum('amount');

        // Total Pendiente (billed - collected for this period's bills)
        $totalPending = max(0, $totalBilled - $totalCollected);

        // Current period expenses
        $totalExpenses = Expense::where('condominium_id', $condominiumId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');

        // Fondo del Condominio (global accumulated: all-time cobrado - gastos + ajustes)
        $allTimeCollected = Payment::where('condominium_id', $condominiumId)
            ->where('status', 'confirmed')
            ->sum('amount');

        $allTimeExpenses = Expense::where('condominium_id', $condominiumId)
            ->sum('amount');

        $allTimeAdjustments = FinancialMovement::where('condominium_id', $condominiumId)
            ->where('movement_type', 'adjustment')
            ->sum('amount');

        $condoFund = $allTimeCollected - $allTimeExpenses + $allTimeAdjustments;

        // Previous period for growth comparison
        $prevDate = $date->copy()->subMonth();
        $prevBilled = MonthlyBill::where('condominium_id', $condominiumId)
            ->where('billing_month', $prevDate->month)
            ->where('billing_year', $prevDate->year)
            ->sum('total');

        $prevCollected = Payment::where('condominium_id', $condominiumId)
            ->where('status', 'confirmed')
            ->whereMonth('payment_date', $prevDate->month)
            ->whereYear('payment_date', $prevDate->year)
            ->sum('amount');

        $prevExpenses = Expense::where('condominium_id', $condominiumId)
            ->whereMonth('date', $prevDate->month)
            ->whereYear('date', $prevDate->year)
            ->sum('amount');

        $hasPrevData = ($prevBilled > 0 || $prevCollected > 0 || $prevExpenses > 0);
        $billedChange = $hasPrevData && $prevBilled > 0 ? round((($totalBilled - $prevBilled) / $prevBilled) * 100) : null;
        $collectedChange = $hasPrevData && $prevCollected > 0 ? round((($totalCollected - $prevCollected) / $prevCollected) * 100) : null;
        $expensesChange = $hasPrevData && $prevExpenses > 0 ? round((($totalExpenses - $prevExpenses) / $prevExpenses) * 100) : null;

        $aptsInDebt = Apartment::where('condominium_id', $condominiumId)
            ->where('balance', '<', 0)
            ->count();

        $pendingPayments = Payment::where('condominium_id', $condominiumId)
            ->where('status', 'pending')
            ->count();

        // Collection percentage (cobrado / facturado)
        $collectionPct = $totalBilled > 0 ? round(($totalCollected / $totalBilled) * 100) : 0;

        // Monthly chart data (6 months ending in selected month)
        $monthlyData = [];
        $maxVal = 1;
        for ($i = 5; $i >= 0; $i--) {
            $chartDate = $date->copy()->subMonths($i);
            $chartBilled = MonthlyBill::where('condominium_id', $condominiumId)
                ->where('billing_month', $chartDate->month)
                ->where('billing_year', $chartDate->year)
                ->sum('total');

            $chartCollected = Payment::where('condominium_id', $condominiumId)
                ->where('status', 'confirmed')
                ->whereMonth('payment_date', $chartDate->month)
                ->whereYear('payment_date', $chartDate->year)
                ->sum('amount');

            $chartExpense = Expense::where('condominium_id', $condominiumId)
                ->whereMonth('date', $chartDate->month)
                ->whereYear('date', $chartDate->year)
                ->sum('amount');

            $monthlyData[] = [
                'label' => $chartDate->locale(app()->getLocale())->shortMonthName,
                'year' => $chartDate->year,
                'month' => $chartDate->month,
                'billed' => $chartBilled,
                'collected' => $chartCollected,
                'expense' => $chartExpense,
                'billed_pct' => 0,
                'collected_pct' => 0,
                'expense_pct' => 0,
            ];

            $maxVal = max($maxVal, $chartBilled, $chartCollected, $chartExpense);
        }

        foreach ($monthlyData as $i => $m) {
            $monthlyData[$i]['billed_pct'] = $maxVal > 0 ? round(($m['billed'] / $maxVal) * 100) : 0;
            $monthlyData[$i]['collected_pct'] = $maxVal > 0 ? round(($m['collected'] / $maxVal) * 100) : 0;
            $monthlyData[$i]['expense_pct'] = $maxVal > 0 ? round(($m['expense'] / $maxVal) * 100) : 0;
        }

        // Recent movements (filtered by selected month)
        $recentMovements = Payment::where('condominium_id', $condominiumId)
            ->whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year)
            ->with(['apartment', 'user', 'bill.billItems'])
            ->orderBy('payment_date', 'desc')
            ->limit(15)
            ->get()
            ->map(function ($payment) {
                $concept = __('messages.common.concept');
                if ($payment->bill && $payment->bill->billItems->isNotEmpty()) {
                    $concept = $payment->bill->billItems->first()->description;
                } elseif ($payment->apartment) {
                    $concept = __('messages.common.apartment') . ' ' . $payment->apartment->number . ' — ' . __('messages.payments.payment');
                }

                return [
                    'apartment' => $payment->apartment ? $payment->apartment->number : '-',
                    'resident' => $payment->user ? $payment->user->name : '-',
                    'concept' => $concept,
                    'amount' => $payment->amount,
                    'date' => $payment->payment_date ? $payment->payment_date->format('d M Y') : '-',
                    'status' => $payment->status,
                    'id' => $payment->id,
                ];
            })->toArray();

        // Available months for filter
        $availableMonths = [];
        for ($m = 1; $m <= 12; $m++) {
            $availableMonths[$m] = now()->setDate($year, $m, 1)->locale(app()->getLocale())->monthName;
        }

        return view('dashboard.index', compact(
            'totalBilled',
            'totalCollected',
            'totalPending',
            'totalExpenses',
            'condoFund',
            'billedChange',
            'collectedChange',
            'expensesChange',
            'hasPrevData',
            'aptsInDebt',
            'pendingPayments',
            'collectionPct',
            'monthlyData',
            'recentMovements',
            'maxVal',
            'month',
            'year',
            'availableMonths'
        ));
    }
}