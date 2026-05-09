<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Condominium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BankAccountController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $bankAccounts = BankAccount::with('condominium')
            ->when($user->role === 'admin', fn($q) => $q->where('condominium_id', $user->condominium_id))
            ->orderBy('bank_name')
            ->get();

        return view('admin.bank-accounts.index', compact('bankAccounts'));
    }

    public function create(): View
    {
        $condominiums = $this->getCondominiumsForSelect();

        return view('admin.bank-accounts.create', compact('condominiums'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'condominium_id' => 'required|exists:condominiums,id',
            'bank_name' => 'required|string|max:255',
            'account_holder' => 'required|string|max:255',
            'account_number' => 'required|string|max:100',
            'account_type' => 'required|string|max:50',
            'currency' => 'nullable|string|max:10',
            'status' => 'nullable|in:active,inactive',
        ]);

        $this->authorizeCondo($validated['condominium_id']);

        BankAccount::create($validated);

        return redirect()->route('bank-accounts.index')
            ->with('success', __('messages.common.save') . '!');
    }

    public function show(BankAccount $bank_account): View
    {
        $this->authorizeCondo($bank_account->condominium_id);

        $bank_account->load('condominium', 'payments');

        return view('admin.bank-accounts.show', compact('bank_account'));
    }

    public function edit(BankAccount $bank_account): View
    {
        $this->authorizeCondo($bank_account->condominium_id);

        $condominiums = $this->getCondominiumsForSelect();

        return view('admin.bank-accounts.edit', compact('bank_account', 'condominiums'));
    }

    public function update(Request $request, BankAccount $bank_account)
    {
        $this->authorizeCondo($bank_account->condominium_id);

        $validated = $request->validate([
            'condominium_id' => 'required|exists:condominiums,id',
            'bank_name' => 'required|string|max:255',
            'account_holder' => 'required|string|max:255',
            'account_number' => 'required|string|max:100',
            'account_type' => 'required|string|max:50',
            'currency' => 'nullable|string|max:10',
            'status' => 'nullable|in:active,inactive',
        ]);

        $this->authorizeCondo($validated['condominium_id']);

        $bank_account->update($validated);

        return redirect()->route('bank-accounts.show', $bank_account)
            ->with('success', __('messages.common.save') . '!');
    }

    public function destroy(BankAccount $bank_account)
    {
        $this->authorizeCondo($bank_account->condominium_id);

        $bank_account->delete();

        return redirect()->route('bank-accounts.index')
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