<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Condominium;
use App\Models\Expense;
use App\Models\ExtraCharge;
use App\Models\GasReading;
use App\Models\MonthlyBill;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $condominiumId = $user->condominium_id;

        $condominiums = $user->role === 'super_admin'
            ? Condominium::orderBy('name')->get()
            : Condominium::where('id', $condominiumId)->get();

        return view('admin.reports.index', compact('condominiums'));
    }

    public function apartmentStatement(Request $request, $apartment)
    {
        $user = Auth::user();
        $apartment = Apartment::with('condominium')->findOrFail($apartment);

        if ($user->role === 'admin' && $apartment->condominium_id !== $user->condominium_id) {
            abort(403);
        }

        $condominium = $apartment->condominium;

        $bills = MonthlyBill::where('apartment_id', $apartment)
            ->with('billItems')
            ->orderBy('billing_year', 'desc')
            ->orderBy('billing_month', 'desc')
            ->get();

        $payments = Payment::where('apartment_id', $apartment)
            ->with('bankAccount')
            ->orderBy('payment_date', 'desc')
            ->get();

        $totalBills = $bills->sum('total');
        $totalPayments = $payments->where('status', 'confirmed')->sum('amount');
        $balance = $apartment->balance;

        $data = compact('apartment', 'payments', 'bills', 'condominium', 'totalBills', 'totalPayments', 'balance');

        return Pdf::loadView('admin.reports.pdf.apartment_statement', $data)->stream('estado_cuenta_' . $apartment->number . '.pdf');
    }

    public function monthlyIncome(Request $request)
    {
        $user = Auth::user();
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $condominium = $this->getCondominium($request, $user);

        $incomes = Payment::where('condominium_id', $condominium->id)
            ->where('status', 'confirmed')
            ->whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year)
            ->with('apartment', 'user')
            ->orderBy('payment_date', 'desc')
            ->get();

        $totalIncome = $incomes->sum('amount');

        $data = compact('incomes', 'month', 'year', 'condominium', 'totalIncome');

        return Pdf::loadView('admin.reports.pdf.monthly_income', $data)->stream('ingresos_' . $month . '_' . $year . '.pdf');
    }

    public function monthlyExpenses(Request $request)
    {
        $user = Auth::user();
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $condominium = $this->getCondominium($request, $user);

        $expenses = Expense::where('condominium_id', $condominium->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->with('category', 'creator')
            ->orderBy('date', 'desc')
            ->get();

        $totalExpenses = $expenses->sum('amount');

        $data = compact('expenses', 'month', 'year', 'condominium', 'totalExpenses');

        return Pdf::loadView('admin.reports.pdf.monthly_expenses', $data)->stream('egresos_' . $month . '_' . $year . '.pdf');
    }

    public function monthlyBalance(Request $request)
    {
        $user = Auth::user();
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $condominium = $this->getCondominium($request, $user);

        $totalIncome = Payment::where('condominium_id', $condominium->id)
            ->where('status', 'confirmed')
            ->whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year)
            ->sum('amount');

        $totalExpenses = Expense::where('condominium_id', $condominium->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');

        $balance = $totalIncome - $totalExpenses;

        $incomes = Payment::where('condominium_id', $condominium->id)
            ->where('status', 'confirmed')
            ->whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year)
            ->with('apartment')
            ->orderBy('payment_date', 'desc')
            ->get();

        $expenses = Expense::where('condominium_id', $condominium->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->with('category')
            ->orderBy('date', 'desc')
            ->get();

        $data = compact('incomes', 'expenses', 'totalIncome', 'totalExpenses', 'balance', 'month', 'year', 'condominium');

        return Pdf::loadView('admin.reports.pdf.monthly_balance', $data)->stream('balance_' . $month . '_' . $year . '.pdf');
    }

    public function debtors(Request $request)
    {
        $user = Auth::user();

        $condominium = $this->getCondominium($request, $user);

        $apartments = Apartment::where('condominium_id', $condominium->id)
            ->where('balance', '<', 0)
            ->with('users')
            ->orderBy('balance', 'asc')
            ->get();

        $totalDebt = $apartments->sum('balance');

        $data = compact('apartments', 'condominium', 'totalDebt');

        return Pdf::loadView('admin.reports.pdf.debtors', $data)->stream('deudores.pdf');
    }

    public function pendingPayments(Request $request)
    {
        $user = Auth::user();

        $condominium = $this->getCondominium($request, $user);

        $payments = Payment::where('condominium_id', $condominium->id)
            ->where('status', 'pending')
            ->with('apartment', 'user', 'bankAccount')
            ->orderBy('payment_date', 'desc')
            ->get();

        $totalPending = $payments->sum('amount');

        $data = compact('payments', 'condominium', 'totalPending');

        return Pdf::loadView('admin.reports.pdf.pending_payments', $data)->stream('pagos_pendientes.pdf');
    }

    public function gasConsumption(Request $request)
    {
        $user = Auth::user();
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $condominium = $this->getCondominium($request, $user);

        $readings = GasReading::whereHas('apartment', function ($q) use ($condominium) {
            $q->where('condominium_id', $condominium->id);
        })
            ->whereMonth('reading_date_end', $month)
            ->whereYear('reading_date_end', $year)
            ->with('apartment')
            ->orderBy('apartment_id')
            ->get();

        $totalConsumption = $readings->sum('consumption_m3');
        $totalGallons = $readings->sum('gallons');
        $totalGas = $readings->sum('total_gas');

        $data = compact('readings', 'month', 'year', 'condominium', 'totalConsumption', 'totalGallons', 'totalGas');

        return Pdf::loadView('admin.reports.pdf.gas_consumption', $data)->stream('consumo_gas_' . $month . '_' . $year . '.pdf');
    }

    public function extraCharges(Request $request)
    {
        $user = Auth::user();

        $condominium = $this->getCondominium($request, $user);

        $charges = ExtraCharge::where('condominium_id', $condominium->id)
            ->where('status', 'active')
            ->with('extraChargeApartments.apartment')
            ->orderBy('start_year', 'desc')
            ->orderBy('start_month', 'desc')
            ->get();

        $data = compact('charges', 'condominium');

        return Pdf::loadView('admin.reports.pdf.extra_charges', $data)->stream('cuotas_extraordinarias.pdf');
    }

    public function billsStatus(Request $request)
    {
        $user = Auth::user();
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $condominium = $this->getCondominium($request, $user);

        $bills = MonthlyBill::where('condominium_id', $condominium->id)
            ->where('billing_month', $month)
            ->where('billing_year', $year)
            ->with('apartment', 'billItems')
            ->orderBy('apartment_id')
            ->get();

        $paidBills = $bills->where('status', 'paid');
        $pendingBills = $bills->where('status', '!=', 'paid');
        $totalPaid = $paidBills->sum('total');
        $totalPending = $pendingBills->sum('total');

        $data = compact('bills', 'paidBills', 'pendingBills', 'totalPaid', 'totalPending', 'month', 'year', 'condominium');

        return Pdf::loadView('admin.reports.pdf.bills_status', $data)->stream('facturas_' . $month . '_' . $year . '.pdf');
    }

    private function getCondominium(Request $request, $user): Condominium
    {
        $condominiumId = $request->input('condominium_id', $user->condominium_id);

        if (!$condominiumId) {
            $first = Condominium::first();
            if ($first) {
                $condominiumId = $first->id;
            } else {
                abort(404, __('messages.common.no_condominiums'));
            }
        }

        if ($user->role === 'admin' && (int) $condominiumId !== $user->condominium_id) {
            abort(403);
        }

        return Condominium::findOrFail($condominiumId);
    }

    private function authorizeCondo(int $condominiumId): void
    {
        $user = Auth::user();
        if ($user->role === 'admin' && $condominiumId !== $user->condominium_id) {
            abort(403, __('messages.auth.unauthorized'));
        }
    }
}