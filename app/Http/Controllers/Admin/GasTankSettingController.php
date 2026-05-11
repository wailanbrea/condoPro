<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Condominium;
use App\Models\GasDelivery;
use App\Models\GasReading;
use App\Models\GasTankSetting;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GasTankSettingController extends Controller
{
    private AuditLogService $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }

    public function edit(): View
    {
        $user = Auth::user();

        $condominiums = $user->role === 'super_admin'
            ? Condominium::orderBy('name')->get()
            : Condominium::where('id', $user->condominium_id)->get();

        $condoId = $user->role === 'admin'
            ? $user->condominium_id
            : (request('condominium_id') ?: $condominiums->first()?->id);

        $setting = $condoId
            ? GasTankSetting::getForCondominium($condoId)
            : null;

        $tankData = null;
        $deliveries = collect();
        if ($condoId && $setting && $setting->status === 'active') {
            $totalConsumption = GasReading::where('condominium_id', $condoId)
                ->sum('gallons');

            $lastDelivery = GasDelivery::where('condominium_id', $condoId)
                ->where('status', 'completed')
                ->orderBy('delivery_date', 'desc')
                ->first();

            $totalDelivered = GasDelivery::where('condominium_id', $condoId)
                ->where('status', 'completed')
                ->sum('gallons_delivered');

            $estimatedInventory = max(0, (float) $setting->capacity_gallons - (float) $totalConsumption + (float) $totalDelivered);
            $estimatedInventory = min($estimatedInventory, (float) $setting->capacity_gallons);
            $percentage = $setting->capacity_gallons > 0
                ? round(($estimatedInventory / (float) $setting->capacity_gallons) * 100, 1)
                : 0;

            $status = 'normal';
            $statusLabel = 'Normal';
            if ($estimatedInventory <= (float) $setting->alert_min_gallons || $percentage <= (float) $setting->alert_min_percentage) {
                $status = 'low';
                $statusLabel = 'Nivel Bajo';
            }

            $monthsBack = match ($setting->average_consumption_method) {
                'last_6_months' => 6,
                'last_12_months' => 12,
                default => 3,
            };

            $monthlyConsumption = GasReading::where('condominium_id', $condoId)
                ->where('created_at', '>=', now()->subMonths($monthsBack))
                ->sum('gallons');

            $monthsWithData = max(1, GasReading::where('condominium_id', $condoId)
                ->where('created_at', '>=', now()->subMonths($monthsBack))
                ->selectRaw('COUNT(DISTINCT CONCAT(billing_month, billing_year)) as cnt')
                ->value('cnt') ?? 1);

            $avgMonthlyConsumption = $monthlyConsumption / $monthsWithData;
            $dailyAverage = $avgMonthlyConsumption / 30;
            $estimatedDays = $dailyAverage > 0 ? (int) round($estimatedInventory / $dailyAverage) : 0;

            $consumptionByMonth = GasReading::where('condominium_id', $condoId)
                ->where('created_at', '>=', now()->subMonths(6))
                ->selectRaw('billing_month, billing_year, SUM(gallons) as total_gallons')
                ->groupBy('billing_month', 'billing_year')
                ->orderBy('billing_year')->orderBy('billing_month')
                ->get();

            $deliveriesByMonth = GasDelivery::where('condominium_id', $condoId)
                ->where('status', 'completed')
                ->where('created_at', '>=', now()->subMonths(6))
                ->selectRaw('MONTH(delivery_date) as month, YEAR(delivery_date) as year, SUM(gallons_delivered) as total_gallons, SUM(invoice_amount) as total_amount')
                ->groupByRaw('MONTH(delivery_date), YEAR(delivery_date)')
                ->orderByRaw('YEAR(delivery_date), MONTH(delivery_date)')
                ->get();

            $tankData = [
                'capacity' => (float) $setting->capacity_gallons,
                'totalConsumption' => (float) $totalConsumption,
                'totalDelivered' => (float) $totalDelivered,
                'estimatedInventory' => round($estimatedInventory, 1),
                'percentage' => $percentage,
                'status' => $status,
                'statusLabel' => $statusLabel,
                'monthlyConsumption' => round($avgMonthlyConsumption, 1),
                'dailyAverage' => round($dailyAverage, 2),
                'estimatedDays' => $estimatedDays,
                'lastDeliveryDate' => $lastDelivery?->delivery_date?->format('d M Y'),
                'consumptionByMonth' => $consumptionByMonth,
                'deliveriesByMonth' => $deliveriesByMonth,
            ];

            $deliveries = GasDelivery::with('condominium', 'creator')
                ->where('condominium_id', $condoId)
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        }

        return view('admin.gas-tank.edit', compact('condominiums', 'condoId', 'setting', 'tankData', 'deliveries'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'condominium_id' => 'required|exists:condominiums,id',
            'tank_name' => 'required|string|max:255',
            'capacity_gallons' => 'required|numeric|gt:0',
            'alert_min_gallons' => 'required|numeric|gte:0',
            'alert_min_percentage' => 'required|numeric|gte:0|lte:100',
            'average_consumption_method' => 'required|in:last_3_months,last_6_months,last_12_months',
            'status' => 'required|in:active,inactive',
        ]);

        if ((float) $validated['alert_min_gallons'] > (float) $validated['capacity_gallons']) {
            return back()->withErrors(['alert_min_gallons' => 'La alerta mínima de galones no puede ser mayor que la capacidad del tanque.'])->withInput();
        }

        if ($user->role === 'admin' && (int) $validated['condominium_id'] !== $user->condominium_id) {
            abort(403);
        }

        $setting = GasTankSetting::updateOrCreate(
            ['condominium_id' => $validated['condominium_id']],
            [
                'tank_name' => $validated['tank_name'],
                'capacity_gallons' => $validated['capacity_gallons'],
                'alert_min_gallons' => $validated['alert_min_gallons'],
                'alert_min_percentage' => $validated['alert_min_percentage'],
                'average_consumption_method' => $validated['average_consumption_method'],
                'status' => $validated['status'],
            ]
        );

        $this->auditLog->log('gas_tank_settings_updated', 'gas', $setting->id);

        return redirect()->route('gas-tank.edit', ['condominium_id' => $validated['condominium_id']])
            ->with('success', 'Configuración del tanque actualizada correctamente.');
    }

    public function reset(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'condominium_id' => 'required|exists:condominiums,id',
        ]);

        if ($user->role === 'admin' && (int) $validated['condominium_id'] !== $user->condominium_id) {
            abort(403);
        }

        $setting = GasTankSetting::where('condominium_id', $validated['condominium_id'])->first();

        if ($setting) {
            $setting->update([
                'tank_name' => 'Tanque Principal',
                'capacity_gallons' => 100,
                'alert_min_gallons' => 20,
                'alert_min_percentage' => 20,
                'average_consumption_method' => 'last_3_months',
                'status' => 'active',
            ]);

            $this->auditLog->log('gas_tank_settings_reset', 'gas', $setting->id);
        }

        return redirect()->route('gas-tank.edit', ['condominium_id' => $validated['condominium_id']])
            ->with('success', 'Configuración restaurada a valores por defecto.');
    }
}