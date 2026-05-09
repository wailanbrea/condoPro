<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Condominium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $users = User::with('condominium', 'apartments')
            ->when($user->role === 'admin', fn($q) => $q->where('condominium_id', $user->condominium_id))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $condominiums = $this->getCondominiumsForSelect();

        return view('admin.users.create', compact('condominiums'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:super_admin,admin,resident',
            'condominium_id' => 'required|exists:condominiums,id',
            'phone' => 'nullable|string|max:50',
            'status' => 'nullable|in:active,inactive',
        ]);

        $this->authorizeCondo($validated['condominium_id']);

        if (Auth::user()->role !== 'super_admin' && $validated['role'] === 'super_admin') {
            abort(403, __('messages.auth.unauthorized'));
        }

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', __('messages.common.save') . '!');
    }

    public function show(User $user): View
    {
        $this->authorizeCondo($user->condominium_id);

        $user->load('condominium', 'apartments', 'payments');

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $this->authorizeCondo($user->condominium_id);

        $condominiums = $this->getCondominiumsForSelect();

        return view('admin.users.edit', compact('user', 'condominiums'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeCondo($user->condominium_id);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:super_admin,admin,resident',
            'condominium_id' => 'required|exists:condominiums,id',
            'phone' => 'nullable|string|max:50',
            'status' => 'nullable|in:active,inactive',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }

        $validated = $request->validate($rules);

        $this->authorizeCondo($validated['condominium_id']);

        if (Auth::user()->role !== 'super_admin' && $validated['role'] === 'super_admin') {
            abort(403, __('messages.auth.unauthorized'));
        }

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.show', $user)
            ->with('success', __('messages.common.save') . '!');
    }

    public function destroy(User $user)
    {
        $this->authorizeCondo($user->condominium_id);

        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')
                ->with('error', __('messages.auth.unauthorized'));
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', __('messages.common.delete') . '!');
    }

    private function authorizeCondo(?int $condominiumId): void
    {
        $authUser = Auth::user();
        if ($authUser->role === 'admin' && $condominiumId !== $authUser->condominium_id) {
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