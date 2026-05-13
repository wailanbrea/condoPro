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
use Barryvdh\DomPDF\Facade\Pdf;
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

    public function index(Request $request): View
    {
        $user = Auth::user();
        $month = $request->filled('month') ? (int) $request->input('month') : null;
        $year = $request->filled('year') ? (int) $request->input('year') : null;
        $type = $request->input('type', 'all');
        $condoId = $request->input('condominium_id');

        $condominiums = $user->role === 'super_admin'
            ? Condominium::orderBy('name')->get()
            : Condominium::where('id', $user->condominium_id)->get();

        if (!$condoId) {
            $condoId = $condominiums->first()?->id;
        }

        if ($user->role === 'admin') {
            $condoId = $user->condominium_id;
        }

        $bills = MonthlyBill::with('apartment.users', 'condominium', 'billItems.gasReading', 'billItems.extraCharge', 'payments')
            ->when($month, fn($q) => $q->where('billing_month', $month))
            ->when($year, fn($q) => $q->where('billing_year', $year))
            ->when($condoId, fn($q) => $q->where('condominium_id', $condoId))
            ->when($type && $type !== 'all', function ($q) use ($type) {
                $q->whereHas('billItems', fn($q2) => $q2->where('concept_type', $type));
            })
            ->orderBy('billing_year', 'desc')
            ->orderBy('billing_month', 'desc')
            ->orderBy('apartment_id')
            ->get();

        $grouped = $bills->groupBy(function ($bill) {
            $user = $bill->apartment?->users?->first();
            return $user ? $user->id : 'none_' . $bill->apartment_id;
        })->map(function ($bills, $key) {
            $firstBill = $bills->first();
            $user = $firstBill->apartment?->users?->first();
            $apartments = $bills->pluck('apartment')->unique('id');

            $pendingStatuses = ['pending', 'partial', 'overdue'];

            return (object) [
                'user_id' => $user?->id,
                'user_name' => $user?->name ?? $firstBill->apartment?->owner_name ?? 'Sin propietario',
                'apartment_numbers' => $apartments->pluck('number')->implode(', '),
                'invoices_count' => $bills->count(),
                'pending_invoices_count' => $bills->whereIn('status', $pendingStatuses)->count(),
                'paid_invoices_count' => $bills->where('status', 'paid')->count(),
                'total_billed' => $bills->sum('total'),
                'total_paid' => $bills->sum('payments_applied'),
                'total_pending' => $bills->sum(fn($b) => max(0, $b->total - $b->payments_applied)),
                'bills' => $bills,
            ];
        })->sortBy('user_name');

        $summary = (object) [
            'users_count' => $grouped->count(),
            'invoices_count' => $bills->count(),
            'pending_invoices_count' => $bills->whereIn('status', ['pending', 'partial', 'overdue'])->count(),
            'total_billed' => $bills->sum('total'),
            'total_paid' => $bills->sum('payments_applied'),
            'total_pending' => $bills->sum(fn($b) => max(0, $b->total - $b->payments_applied)),
        ];

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = ucfirst(\Carbon\Carbon::create()->month($m)->locale('es')->monthName);
        }

        return view('admin.billing.index', compact('bills', 'grouped', 'summary', 'month', 'year', 'type', 'condoId', 'condominiums', 'months'));
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

            $outstandingBalance = MonthlyBill::where('apartment_id', $apartment->id)
                ->whereIn('status', ['pending', 'partial', 'overdue'])
                ->selectRaw('SUM(GREATEST(0, total - payments_applied)) as total_outstanding')
                ->value('total_outstanding') ?? 0;

            $bill = MonthlyBill::create([
                'condominium_id' => $validated['condominium_id'],
                'apartment_id' => $apartment->id,
                'billing_month' => $validated['billing_month'],
                'billing_year' => $validated['billing_year'],
                'subtotal' => 0,
                'previous_balance' => $outstandingBalance,
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
                'total' => $subtotal + $outstandingBalance,
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

    public function downloadPdf(MonthlyBill $billing)
    {
        $this->authorizeCondo($billing->condominium_id);

        $billing->load('apartment', 'condominium', 'billItems', 'payments');

        $data = [
            'bill' => $billing,
            'condominium' => $billing->condominium,
            'apartment' => $billing->apartment,
        ];

        $fileName = 'factura_' . $billing->apartment->number . '_' .
            str_pad($billing->billing_month, 2, '0', STR_PAD_LEFT) . '_' .
            $billing->billing_year . '.pdf';

        return Pdf::loadView('admin.billing.pdf', $data)->stream($fileName);
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