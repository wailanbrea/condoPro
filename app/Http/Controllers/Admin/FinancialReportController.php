<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\BillItem;
use App\Models\Condominium;
use App\Models\Expense;
use App\Models\ExtraChargeInstallment;
use App\Models\FinancialMovement;
use App\Models\GasReading;
use App\Models\MonthlyBill;
use App\Models\MonthlyFinancialReport;
use App\Models\Payment;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FinancialReportController extends Controller
{
    private AuditLogService $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }

    public function index(): View
    {
        $user = Auth::user();
        $condominiums = $user->role === 'super_admin'
            ? Condominium::orderBy('name')->get()
            : Condominium::where('id', $user->condominium_id)->get();

        $selectedCondo = $user->role === 'admin'
            ? Condominium::find($user->condominium_id)
            : $condominiums->first();

        $month = (int) request('month', now()->month);
        $year = (int) request('year', now()->year);

        if ($selectedCondo) {
            $data = $this->buildFinancialData($selectedCondo->id, $month, $year);
        } else {
            $data = $this->emptyFinancialData();
        }

        $reports = MonthlyFinancialReport::with('condominium', 'creator')
            ->when($user->role === 'admin', fn($q) => $q->where('condominium_id', $user->condominium_id))
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(12);

        return view('admin.financial-reports.index', array_merge($data, [
            'condominiums' => $condominiums,
            'selectedCondo' => $selectedCondo,
            'month' => $month,
            'year' => $year,
            'reports' => $reports,
        ]));
    }

    public function show(MonthlyFinancialReport $financialReport): View
    {
        $this->authorizeCondo($financialReport->condominium_id);

        $financialReport->load('condominium', 'creator', 'closer');
        $data = $this->buildFinancialData($financialReport->condominium_id, $financialReport->month, $financialReport->year);

        return view('admin.financial-reports.show', array_merge($data, [
            'report' => $financialReport,
        ]));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'condominium_id' => 'required|exists:condominiums,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
            'initial_balance' => 'required|numeric',
            'special_payments' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $this->authorizeCondo($validated['condominium_id']);

        $exists = MonthlyFinancialReport::where('condominium_id', $validated['condominium_id'])
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['month' => __('messages.financial.report_exists')])->withInput();
        }

        $report = new MonthlyFinancialReport($validated);
        $report->status = 'open';
        $report->created_by = Auth::id();
        $report->special_payments = $validated['special_payments'] ?? 0;
        $report->calculateFromData();
        $report->save();

        $this->auditLog->log('financial_report_generated', 'financial_reports', $report->id, null, $report->toArray());

        return redirect()->route('financial-reports.show', $report)
            ->with('success', __('messages.financial.report_generated'));
    }

    public function close(MonthlyFinancialReport $financialReport)
    {
        $this->authorizeCondo($financialReport->condominium_id);

        if ($financialReport->status === 'closed') {
            return back()->with('error', __('messages.financial.report_already_closed'));
        }

        $financialReport->update([
            'status' => 'closed',
            'closed_by' => Auth::id(),
            'closed_at' => now(),
        ]);

        $this->auditLog->log('financial_report_closed', 'financial_reports', $financialReport->id);

        return back()->with('success', __('messages.financial.report_closed'));
    }

    public function storeMovement(Request $request)
    {
        $validated = $request->validate([
            'condominium_id' => 'required|exists:condominiums,id',
            'movement_type' => 'required|in:income,expense,adjustment',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'movement_date' => 'required|date',
        ]);

        $this->authorizeCondo($validated['condominium_id']);

        $validated['month'] = (int) now()->parse($validated['movement_date'])->format('n');
        $validated['year'] = (int) now()->parse($validated['movement_date'])->format('Y');
        $validated['created_by'] = Auth::id();

        $movement = FinancialMovement::create($validated);

        $this->auditLog->log('financial_movement_created', 'financial_movements', $movement->id, null, $movement->toArray());

        return back()->with('success', __('messages.financial.movement_created'));
    }

    public function destroyMovement(FinancialMovement $movement)
    {
        $this->authorizeCondo($movement->condominium_id);

        $oldValues = $movement->toArray();
        $movement->delete();

        $this->auditLog->log('financial_movement_deleted', 'financial_movements', $movement->id, $oldValues, null);

        return back()->with('success', __('messages.financial.movement_deleted'));
    }

    private function buildFinancialData(int $condominiumId, int $month, int $year): array
    {
        $bills = MonthlyBill::with('apartment', 'billItems')
            ->where('condominium_id', $condominiumId)
            ->where('billing_month', $month)
            ->where('billing_year', $year)
            ->get();

        $apartments = Apartment::where('condominium_id', $condominiumId)
            ->where('status', 'active')
            ->orderBy('number')
            ->get();

        $incomeRows = [];
        $totalMaintenance = 0;
        $totalGas = 0;
        $totalExtraCharges = 0;
        $totalIncome = 0;
        $totalPending = 0;

        foreach ($apartments as $apt) {
            $bill = $bills->first(fn($b) => $b->apartment_id === $apt->id);

            $maintenance = 0;
            $gas = 0;
            $extraCharges = 0;

            if ($bill) {
                $maintenance = $bill->billItems->where('concept_type', 'maintenance')->sum('amount');
                $gas = $bill->billItems->where('concept_type', 'gas')->sum('amount');
                $extraCharges = $bill->billItems->where('concept_type', 'extra_charge')->sum('amount');
            }

            $rowTotal = $maintenance + $gas + $extraCharges;
            $pending = $bill ? max(0, $bill->total - $bill->payments_applied) : $apt->maintenance_fee;

            $incomeRows[] = [
                'apartment' => $apt,
                'maintenance' => $maintenance,
                'gas' => $gas,
                'extra_charges' => $extraCharges,
                'total' => $rowTotal,
                'pending' => $pending,
                'bill' => $bill,
            ];

            $totalMaintenance += $maintenance;
            $totalGas += $gas;
            $totalExtraCharges += $extraCharges;
            $totalIncome += $rowTotal;
            $totalPending += $pending;
        }

        $expenses = Expense::with('category')
            ->where('condominium_id', $condominiumId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->get();
        $totalExpenses = $expenses->sum('amount');

        $movements = FinancialMovement::where('condominium_id', $condominiumId)
            ->where('month', $month)
            ->where('year', $year)
            ->orderBy('movement_date', 'desc')
            ->get();
        $totalMovementIncome = $movements->where('movement_type', 'income')->sum('amount');
        $totalMovementExpense = $movements->where('movement_type', 'expense')->sum('amount');
        $totalAdjustments = $movements->where('movement_type', 'adjustment')->sum('amount');

        $existingReport = MonthlyFinancialReport::where('condominium_id', $condominiumId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        $initialBalance = $existingReport?->initial_balance ?? 0;
        $specialPayments = $existingReport?->special_payments ?? 0;
        $finalBalance = $initialBalance + $totalIncome + $totalMovementIncome - $totalExpenses - $totalMovementExpense - $specialPayments + $totalAdjustments;

        return [
            'incomeRows' => $incomeRows,
            'totalMaintenance' => $totalMaintenance,
            'totalGas' => $totalGas,
            'totalExtraCharges' => $totalExtraCharges,
            'totalIncome' => $totalIncome,
            'totalPending' => $totalPending,
            'expenses' => $expenses,
            'totalExpenses' => $totalExpenses,
            'movements' => $movements,
            'totalMovementIncome' => $totalMovementIncome,
            'totalMovementExpense' => $totalMovementExpense,
            'totalAdjustments' => $totalAdjustments,
            'initialBalance' => $initialBalance,
            'specialPayments' => $specialPayments,
            'finalBalance' => $finalBalance,
            'existingReport' => $existingReport,
        ];
    }

    private function emptyFinancialData(): array
    {
        return [
            'incomeRows' => [],
            'totalMaintenance' => 0,
            'totalGas' => 0,
            'totalExtraCharges' => 0,
            'totalIncome' => 0,
            'totalPending' => 0,
            'expenses' => collect(),
            'totalExpenses' => 0,
            'movements' => collect(),
            'totalMovementIncome' => 0,
            'totalMovementExpense' => 0,
            'totalAdjustments' => 0,
            'initialBalance' => 0,
            'specialPayments' => 0,
            'finalBalance' => 0,
            'existingReport' => null,
        ];
    }

    private function authorizeCondo(?int $condominiumId): void
    {
        $user = Auth::user();
        if ($user->role === 'admin' && $condominiumId !== $user->condominium_id) {
            abort(403, __('messages.auth.unauthorized'));
        }
    }
}