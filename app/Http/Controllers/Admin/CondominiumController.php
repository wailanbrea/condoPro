<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Condominium;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CondominiumController extends Controller
{
    private AuditLogService $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }

    public function index(): View
    {
        $condominiums = Auth::user()->role === 'super_admin'
            ? Condominium::orderBy('created_at', 'desc')->get()
            : Condominium::where('id', Auth::user()->condominium_id)->get();

        return view('admin.condominiums.index', compact('condominiums'));
    }

    public function create(): View
    {
        $this->authorizeSuperAdmin();

        return view('admin.condominiums.create');
    }

    public function store(Request $request)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|max:2048',
            'currency' => 'nullable|string|max:10',
            'language_default' => 'nullable|string|max:5',
            'gas_price_per_gallon' => 'nullable|numeric|min:0',
            'gas_conversion_factor' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive',
        ]);

        $condominium = Condominium::create($validated);

        $this->auditLog->log('condominium_created', 'condominiums', $condominium->id);

        return redirect()->route('condominiums.index')
            ->with('success', __('messages.common.save') . '!');
    }

    public function show(Condominium $condominium): View
    {
        $this->authorizeCondo($condominium->id);

        $condominium->load(['apartments', 'users', 'bankAccounts', 'expenseCategories']);

        return view('admin.condominiums.show', compact('condominium'));
    }

    public function edit(Condominium $condominium): View
    {
        $this->authorizeCondo($condominium->id);

        return view('admin.condominiums.edit', compact('condominium'));
    }

    public function update(Request $request, Condominium $condominium)
    {
        $this->authorizeCondo($condominium->id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|max:2048',
            'currency' => 'nullable|string|max:10',
            'language_default' => 'nullable|string|max:5',
            'gas_price_per_gallon' => 'nullable|numeric|min:0',
            'gas_conversion_factor' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive',
        ]);

        $oldValue = $condominium->getOriginal();
        $condominium->update($validated);
        $this->auditLog->log('condominium_updated', 'condominiums', $condominium->id, $oldValue, $validated);

        return redirect()->route('condominiums.show', $condominium)
            ->with('success', __('messages.common.save') . '!');
    }

    public function destroy(Condominium $condominium)
    {
        $this->authorizeSuperAdmin();

        $this->auditLog->log('condominium_deleted', 'condominiums', $condominium->id);

        $condominium->delete();

        return redirect()->route('condominiums.index')
            ->with('success', __('messages.common.delete') . '!');
    }

    private function authorizeCondo(int $condominiumId): void
    {
        $user = Auth::user();
        if ($user->role === 'admin' && $condominiumId !== $user->condominium_id) {
            abort(403, __('messages.auth.unauthorized'));
        }
    }

    private function authorizeSuperAdmin(): void
    {
        if (Auth::user()->role !== 'super_admin') {
            abort(403, __('messages.auth.unauthorized'));
        }
    }
}