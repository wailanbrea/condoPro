<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Condominium;
use App\Models\GasDelivery;
use App\Models\GasReading;
use App\Models\GasTankSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GasInventoryController extends Controller
{
    public function index(): View
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
            $totalConsumption = GasReading::where('condominium_id', $condoId)->sum('gallons');
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

            $avgMethod = $setting->averageConsumption_method;
            $monthsBack = match ($avgMethod) {
                'last_6_months' => 6,
                'last_12_months' => 12,
                default => 3,
            };

            $monthlyConsumption = GasReading::where('condominium_id', $condoId)
                ->where('created_at', '>=', now()->subMonths($monthsBack))
                ->sum('gallons');

            $monthsWithData = GasReading::where('condominium_id', $condoId)
                ->where('created_at', '>=', now()->subMonths($monthsBack))
                ->selectRaw('COUNT(DISTINCT CONCAT(billing_month, billing_year)) as cnt')
                ->value('cnt') ?: 1;

            $avgMonthlyConsumption = $monthsWithData > 0 ? $monthlyConsumption / $monthsWithData : 0;
            $dailyAverage = $avgMonthlyConsumption > 0 ? $avgMonthlyConsumption / 30 : 0;
            $estimatedDays = $dailyAverage > 0 ? (int) round($estimatedInventory / $dailyAverage) : 0;

            $lastDelivery = GasDelivery::where('condominium_id', $condoId)
                ->where('status', 'completed')
                ->orderBy('delivery_date', 'desc')
                ->first();

            $consumptionByMonth = GasReading::where('condominium_id', $condoId)
                ->where('created_at', '>=', now()->subMonths(6))
                ->selectRaw('billing_month, billing_year, SUM(gallons) as total_gallons')
                ->groupBy('billing_month', 'billing_year')
                ->orderBy('billing_year', 'billing_month')
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
                'deliveriesByMonth' => GasDelivery::where('condominium_id', $condoId)
                    ->where('status', 'completed')
                    ->where('created_at', '>=', now()->subMonths(6))
                    ->selectRaw('MONTH(delivery_date) as month, YEAR(delivery_date) as year, SUM(gallons_delivered) as total_gallons, SUM(invoice_amount) as total_amount')
                    ->groupByRaw('MONTH(delivery_date), YEAR(delivery_date)')
                    ->orderByRaw('YEAR(delivery_date), MONTH(delivery_date)')
                    ->get(),
            ];

            $deliveries = GasDelivery::where('condominium_id', $condoId)
                ->where('status', 'completed')
                ->orderBy('delivery_date', 'desc')
                ->paginate(10);
        }

        return view('admin.gas.inventory', compact('condominiums', 'condoId', 'setting', 'tankData', 'deliveries'));
    }
}