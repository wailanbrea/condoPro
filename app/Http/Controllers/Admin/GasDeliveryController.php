<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Condominium;
use App\Models\GasDelivery;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GasDeliveryController extends Controller
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

        $condoId = $user->role === 'admin'
            ? $user->condominium_id
            : (request('condominium_id') ?: $condominiums->first()?->id);

        $deliveries = GasDelivery::with('condominium', 'creator')
            ->when($condoId, fn($q) => $q->where('condominium_id', $condoId))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.gas-deliveries.index', compact('deliveries', 'condominiums', 'condoId'));
    }

    public function show(GasDelivery $gasDelivery): View
    {
        $user = Auth::user();

        if ($user->role === 'admin' && $gasDelivery->condominium_id !== $user->condominium_id) {
            abort(403);
        }

        $gasDelivery->load('condominium', 'creator');

        $totalConsumption = \App\Models\GasReading::where('condominium_id', $gasDelivery->condominium_id)
            ->whereMonth('created_at', $gasDelivery->delivery_date?->month)
            ->whereYear('created_at', $gasDelivery->delivery_date?->year)
            ->sum('gallons');

        return view('admin.gas-deliveries.show', compact('gasDelivery', 'totalConsumption'));
    }
}