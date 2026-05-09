<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Condominium;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ApartmentController extends Controller
{
    private AuditLogService $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }

    public function index(): View
    {
        $condominiumId = Auth::user()->condominium_id;
        $isSuperAdmin = Auth::user()->role === 'super_admin';

        $apartments = Apartment::with('condominium', 'users')
            ->when(!$isSuperAdmin, fn($q) => $q->where('condominium_id', $condominiumId))
            ->orderBy('number')
            ->get();

        return view('admin.apartments.index', compact('apartments'));
    }

    public function create(): View
    {
        $condominiums = $this->getCondominiumsForSelect();

        return view('admin.apartments.create', compact('condominiums'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'condominium_id' => 'required|exists:condominiums,id',
            'number' => 'required|string|max:50',
            'owner_name' => 'nullable|string|max:255',
            'area' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive',
            'balance' => 'nullable|numeric',
            'maintenance_fee' => 'nullable|numeric',
            'has_gas_meter' => 'nullable|boolean',
        ]);

        $this->authorizeCondo($validated['condominium_id']);

        $apartment = Apartment::create($validated);

        $this->auditLog->log('apartment_created', 'apartments', $apartment->id);

        return redirect()->route('apartments.index')
            ->with('success', __('messages.common.save') . '!');
    }

    public function show(Apartment $apartment): View
    {
        $this->authorizeCondo($apartment->condominium_id);

        $apartment->load('condominium', 'users', 'monthlyBills', 'gasReadings', 'payments');

        return view('admin.apartments.show', compact('apartment'));
    }

    public function edit(Apartment $apartment): View
    {
        $this->authorizeCondo($apartment->condominium_id);

        $condominiums = $this->getCondominiumsForSelect();

        return view('admin.apartments.edit', compact('apartment', 'condominiums'));
    }

    public function update(Request $request, Apartment $apartment)
    {
        $this->authorizeCondo($apartment->condominium_id);

        $validated = $request->validate([
            'condominium_id' => 'required|exists:condominiums,id',
            'number' => 'required|string|max:50',
            'owner_name' => 'nullable|string|max:255',
            'area' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive',
            'balance' => 'nullable|numeric',
            'maintenance_fee' => 'nullable|numeric',
            'has_gas_meter' => 'nullable|boolean',
        ]);

        $this->authorizeCondo($validated['condominium_id']);

        $apartment->update($validated);

        $this->auditLog->log('apartment_updated', 'apartments', $apartment->id);

        return redirect()->route('apartments.show', $apartment)
            ->with('success', __('messages.common.save') . '!');
    }

    public function destroy(Apartment $apartment)
    {
        $this->authorizeCondo($apartment->condominium_id);

        $this->auditLog->log('apartment_deleted', 'apartments', $apartment->id);

        $apartment->delete();

        return redirect()->route('apartments.index')
            ->with('success', __('messages.common.delete') . '!');
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
}