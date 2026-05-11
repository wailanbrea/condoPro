<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GasDelivery;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GasDeliveryController extends Controller
{
    private AuditLogService $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
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