<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Condominium;
use App\Models\GasReading;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GasController extends Controller
{
    private AuditLogService $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }

    public function index(): View
    {
        $user = Auth::user();

        $month = (int) request('month', now()->month);
        $year = (int) request('year', now()->year);

        $condominiums = $user->role === 'super_admin'
            ? Condominium::orderBy('name')->get()
            : Condominium::where('id', $user->condominium_id)->get();

        $condoId = $user->role === 'admin'
            ? $user->condominium_id
            : (request('condominium_id') ?: $condominiums->first()?->id);

        $currentReadings = GasReading::with('apartment.condominium', 'condominium', 'creator')
            ->when($condoId, fn($q) => $q->where('condominium_id', $condoId))
            ->where('billing_month', $month)
            ->where('billing_year', $year)
            ->where('billed', false)
            ->orderBy('apartment_id')
            ->get();

        $billedHistory = GasReading::with('apartment.condominium', 'condominium', 'creator')
            ->when($condoId, fn($q) => $q->where('condominium_id', $condoId))
            ->where(function ($q) use ($month, $year) {
                $q->where('billing_month', '!=', $month)
                  ->orWhere('billing_year', '!=', $year)
                  ->orWhere('billed', true);
            })
            ->orderBy('billing_year', 'desc')
            ->orderBy('billing_month', 'desc')
            ->orderBy('apartment_id')
            ->get();

        $condo = Condominium::find($condoId);

        $stats = [
            'apartments_read' => $currentReadings->count(),
            'total_consumption' => $currentReadings->sum('consumption_m3'),
            'total_gallons' => $currentReadings->sum('gallons'),
            'total_amount' => $currentReadings->sum('total_amount'),
            'pending_count' => $currentReadings->where('billed', false)->count(),
            'billed_count' => GasReading::when($condoId, fn($q) => $q->where('condominium_id', $condoId))->where('billed', true)->count(),
            'config_factor' => $currentReadings->first()?->conversion_factor ?? $condo?->gas_conversion_factor ?? 1.20,
            'config_price' => $currentReadings->first()?->gallon_price ?? $condo?->gas_price_per_gallon ?? 147.20,
            'config_extra' => $currentReadings->first()?->extra_cost_per_gallon ?? 0,
            'config_total_price' => $currentReadings->first()?->total_gallon_price ?? (($condo?->gas_price_per_gallon ?? 147.20) + 0),
            'config_start' => $currentReadings->first()?->reading_date_start ? $currentReadings->first()->reading_date_start->format('d/m/Y') : now()->startOfMonth()->format('d/m/Y'),
            'config_end' => $currentReadings->first()?->reading_date_end ? $currentReadings->first()->reading_date_end->format('d/m/Y') : now()->endOfMonth()->format('d/m/Y'),
        ];

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = ucfirst(\Carbon\Carbon::create()->month($m)->locale('es')->monthName);
        }

        return view('admin.gas.index', compact('currentReadings', 'billedHistory', 'condominiums', 'condoId', 'month', 'year', 'months', 'stats'));
    }

    public function create(): View
    {
        $apartments = $this->getApartmentsForSelect();
        $user = Auth::user();
        $condominium = $user->role === 'super_admin'
            ? Condominium::first()
            : Condominium::find($user->condominium_id);

        $gasDefaults = [
            'gallon_price' => $condominium?->gas_price_per_gallon ?? 147.20,
            'conversion_factor' => $condominium?->gas_conversion_factor ?? 1.20,
        ];

        return view('admin.gas.create', compact('apartments', 'gasDefaults'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateGasReading($request);

        $this->authorizeCondo($validated['condominium_id']);

        $this->checkDuplicate($validated);

        if ($validated['reading_final'] < $validated['reading_initial']) {
            return back()->withErrors(['reading_final' => 'La lectura actual no puede ser menor que la lectura anterior.'])->withInput();
        }

        $this->calculateGas($validated);

        $validated['created_by'] = Auth::id();

        $gasReading = GasReading::create($validated);

        $this->auditLog->log('gas_reading_created', 'gas', $gasReading->id, null, $gasReading->toArray());

        return redirect()->route('gas.index')
            ->with('success', 'Lectura de gas registrada correctamente.');
    }

    public function show(GasReading $gas): View
    {
        $this->authorizeCondo($gas->condominium_id);

        $gas->load('apartment.condominium', 'condominium', 'creator');

        return view('admin.gas.show', compact('gas'));
    }

    public function edit(GasReading $gas): View
    {
        $this->authorizeCondo($gas->condominium_id);

        if ($gas->billed) {
            return redirect()->route('gas.index')
                ->with('error', 'No se puede editar una lectura que ya fue facturada.');
        }

        $condominiums = $this->getCondominiumsForSelect();
        $apartments = $this->getApartmentsForSelect();

        return view('admin.gas.edit', compact('gas', 'condominiums', 'apartments'));
    }

    public function update(Request $request, GasReading $gas)
    {
        $this->authorizeCondo($gas->condominium_id);

        if ($gas->billed) {
            return redirect()->route('gas.index')
                ->with('error', 'No se puede editar una lectura que ya fue facturada.');
        }

        $validated = $this->validateGasReading($request);

        $this->authorizeCondo($validated['condominium_id']);

        if ($validated['reading_final'] < $validated['reading_initial']) {
            return back()->withErrors(['reading_final' => 'La lectura actual no puede ser menor que la lectura anterior.'])->withInput();
        }

        $oldValues = $gas->toArray();

        $this->calculateGas($validated);

        $gas->update($validated);

        $this->auditLog->log('gas_reading_updated', 'gas', $gas->id, $oldValues, $gas->fresh()->toArray());

        return redirect()->route('gas.show', $gas)
            ->with('success', 'Lectura actualizada correctamente.');
    }

    public function destroy(GasReading $gas)
    {
        $this->authorizeCondo($gas->condominium_id);

        if ($gas->billed) {
            return redirect()->route('gas.index')
                ->with('error', 'No se puede eliminar una lectura que ya fue facturada.');
        }

        $oldValues = $gas->toArray();
        $gas->delete();

        $this->auditLog->log('gas_reading_deleted', 'gas', $gas->id, $oldValues, null);

        return redirect()->route('gas.index')
            ->with('success', 'Lectura eliminada correctamente.');
    }

    private function validateGasReading(Request $request): array
    {
        return $request->validate([
            'condominium_id' => 'required|exists:condominiums,id',
            'apartment_id' => 'required|exists:apartments,id',
            'meter_number' => 'nullable|string|max:50',
            'reading_date_start' => 'required|date',
            'reading_date_end' => 'required|date|after_or_equal:reading_date_start',
            'billing_month' => 'required|integer|min:1|max:12',
            'billing_year' => 'required|integer|min:2020',
            'reading_initial' => 'required|numeric|min:0',
            'reading_final' => 'required|numeric|min:0',
            'conversion_factor' => 'required|numeric|min:0',
            'gallon_price' => 'required|numeric|min:0',
            'extra_cost_per_gallon' => 'nullable|numeric|min:0',
        ]);
    }

    private function checkDuplicate(array $validated): void
    {
        $exists = GasReading::where('apartment_id', $validated['apartment_id'])
            ->where('billing_month', $validated['billing_month'])
            ->where('billing_year', $validated['billing_year'])
            ->exists();

        if ($exists) {
            abort(422, 'Ya existe una lectura de gas para este apartamento en el período seleccionado.');
        }
    }

    private function calculateGas(array &$validated): void
    {
        $validated['consumption_m3'] = round($validated['reading_final'] - $validated['reading_initial'], 3);
        $validated['gallons'] = round($validated['consumption_m3'] * $validated['conversion_factor'], 2);
        $validated['extra_cost_per_gallon'] = $validated['extra_cost_per_gallon'] ?? 0;
        $validated['total_gallon_price'] = round($validated['gallon_price'] + $validated['extra_cost_per_gallon'], 2);
        $validated['total_amount'] = round($validated['gallons'] * $validated['total_gallon_price'], 2);
        $validated['total_gas'] = $validated['total_amount'];
        $validated['price_per_gallon'] = $validated['gallon_price'];
        $validated['status'] = $validated['status'] ?? 'active';
    }

    private function authorizeCondo(?int $condominiumId): void
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
            return Apartment::with('condominium')
                ->where('status', 'active')
                ->orderBy('number')
                ->get()
                ->mapWithKeys(fn($a) => [$a->id => $a->condominium->name . ' - ' . $a->number]);
        }
        return Apartment::where('condominium_id', $user->condominium_id)
            ->where('status', 'active')
            ->orderBy('number')
            ->pluck('number', 'id');
    }
}