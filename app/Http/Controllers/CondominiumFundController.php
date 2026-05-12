<?php

namespace App\Http\Controllers;

use App\Models\Condominium;
use App\Models\Expense;
use App\Models\FinancialMovement;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CondominiumFundController extends Controller
{
    public function history(Request $request): View
    {
        $user = Auth::user();
        $condominiumId = $user->condominium_id;

        if ($user->role === 'super_admin' && !$condominiumId) {
            $firstCondo = Condominium::first();
            $condominiumId = $firstCondo?->id;
        }

        $condominium = Condominium::find($condominiumId);

        $year = (int) ($request->input('year', now()->year));

        $months = [];
        $runningBalance = 0;

        $allPayments = Payment::where('condominium_id', $condominiumId)
            ->where('status', 'confirmed')
            ->whereYear('payment_date', $year)
            ->selectRaw('MONTH(payment_date) as m, SUM(amount) as total')
            ->groupByRaw('MONTH(payment_date)')
            ->pluck('total', 'm')
            ->toArray();

        $allExpenses = Expense::where('condominium_id', $condominiumId)
            ->whereYear('date', $year)
            ->selectRaw('MONTH(date) as m, SUM(amount) as total')
            ->groupByRaw('MONTH(date)')
            ->pluck('total', 'm')
            ->toArray();

        $allAdjustments = FinancialMovement::where('condominium_id', $condominiumId)
            ->where('movement_type', 'adjustment')
            ->whereYear('movement_date', $year)
            ->selectRaw('MONTH(movement_date) as m, SUM(amount) as total')
            ->groupByRaw('MONTH(movement_date)')
            ->pluck('total', 'm')
            ->toArray();

        $prevCollected = Payment::where('condominium_id', $condominiumId)
            ->where('status', 'confirmed')
            ->where('payment_date', '<', $year . '-01-01')
            ->sum('amount');

        $prevExpenses = Expense::where('condominium_id', $condominiumId)
            ->where('date', '<', $year . '-01-01')
            ->sum('amount');

        $prevAdjustments = FinancialMovement::where('condominium_id', $condominiumId)
            ->where('movement_type', 'adjustment')
            ->where('movement_date', '<', $year . '-01-01')
            ->sum('amount');

        $runningBalance = $prevCollected - $prevExpenses + $prevAdjustments;

        $monthNames = [
            1 => __('messages.common.january'), 2 => __('messages.common.february'), 3 => __('messages.common.march'), 4 => __('messages.common.april'),
            5 => __('messages.common.may'), 6 => __('messages.common.june'), 7 => __('messages.common.july'), 8 => __('messages.common.august'),
            9 => __('messages.common.september'), 10 => __('messages.common.october'), 11 => __('messages.common.november'), 12 => __('messages.common.december'),
        ];

        for ($m = 1; $m <= 12; $m++) {
            $income = $allPayments[$m] ?? 0;
            $expenses = $allExpenses[$m] ?? 0;
            $adjustments = $allAdjustments[$m] ?? 0;
            $net = $income - $expenses + $adjustments;

            $months[] = [
                'month' => $m,
                'name' => $monthNames[$m],
                'income' => $income,
                'expenses' => $expenses,
                'adjustments' => $adjustments,
                'net' => $net,
                'balance' => $runningBalance + $net,
            ];

            $runningBalance += $net;
        }

        $finalBalance = $runningBalance;

        // Detailed movements for the selected year (withdrawals, deposits, adjustments)
        $movements = FinancialMovement::where('condominium_id', $condominiumId)
            ->whereYear('movement_date', $year)
            ->orderBy('movement_date', 'desc')
            ->get();

        $expensesDetail = Expense::with('category', 'creator')
            ->where('condominium_id', $condominiumId)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->get();

        $isAdmin = in_array($user->role, ['super_admin', 'admin']);
        $layout = $isAdmin ? 'layouts.app' : 'layouts.resident';

        $success = session('success');
        $error = session('error');

        return view('condominium-fund.history', compact(
            'condominium',
            'months',
            'year',
            'finalBalance',
            'layout',
            'isAdmin',
            'movements',
            'expensesDetail',
            'success',
            'error'
        ));
    }

    public function withdraw(Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['super_admin', 'admin'])) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'movement_date' => 'required|date',
        ]);

        $condominiumId = $user->condominium_id;
        if ($user->role === 'super_admin' && !$condominiumId) {
            $condominiumId = Condominium::first()?->id;
        }

        FinancialMovement::create([
            'condominium_id' => $condominiumId,
            'movement_type' => 'adjustment',
            'category' => 'correction',
            'amount' => -abs($validated['amount']),
            'description' => __('messages.fund.withdrawal_prefix') . ' ' . $validated['description'],
            'movement_date' => $validated['movement_date'],
            'month' => (int) date('n', strtotime($validated['movement_date'])),
            'year' => (int) date('Y', strtotime($validated['movement_date'])),
            'created_by' => $user->id,
        ]);

        $route = in_array($user->role, ['super_admin', 'admin'])
            ? 'condominium-fund.history'
            : 'resident.condominium-fund';

        return redirect()->route($route)
            ->with('success', __('messages.fund.withdrawal_success'));
    }
}