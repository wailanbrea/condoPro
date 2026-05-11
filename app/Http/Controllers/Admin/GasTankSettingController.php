<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Condominium;
use App\Models\GasTankSetting;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GasTankSettingController extends Controller
{
    private AuditLogService $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }

    public function edit(): RedirectResponse
    {
        $user = Auth::user();
        $condoId = $user->role === 'admin'
            ? $user->condominium_id
            : (request('condominium_id') ?? null);

        if ($condoId) {
            return redirect()->route('gas.index', ['condominium_id' => $condoId]);
        }

        $condominiums = $user->role === 'super_admin'
            ? Condominium::orderBy('name')->get()
            : Condominium::where('id', $user->condominium_id)->get();

        return redirect()->route('gas.index', ['condominium_id' => $condominiums->first()?->id]);
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

        return redirect()->route('gas.index', ['condominium_id' => $validated['condominium_id']])
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

        return redirect()->route('gas.index', ['condominium_id' => $validated['condominium_id']])
            ->with('success', 'Configuración restaurada a valores por defecto.');
    }
}