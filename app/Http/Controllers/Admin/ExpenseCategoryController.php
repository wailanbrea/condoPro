<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Condominium;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $categories = ExpenseCategory::with('condominium', 'expenses')
            ->when($user->role === 'admin', fn($q) => $q->where('condominium_id', $user->condominium_id))
            ->orderBy('name')
            ->paginate(20);

        return view('admin.expense-categories.index', compact('categories'));
    }

    public function create()
    {
        $condominiums = $this->getCondominiumsForSelect();
        return view('admin.expense-categories.create', compact('condominiums'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'condominium_id' => 'required|exists:condominiums,id',
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ]);

        $this->authorizeCondo($validated['condominium_id']);

        $validated['status'] = $validated['status'] ?? 'active';

        ExpenseCategory::create($validated);

        return redirect()->route('expense-categories.index')
            ->with('success', __('messages.common.save') . '!');
    }

    public function show(ExpenseCategory $expenseCategory)
    {
        $this->authorizeCondo($expenseCategory->condominium_id);

        $expenseCategory->load('condominium', 'expenses');

        return view('admin.expense-categories.show', compact('expenseCategory'));
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        $this->authorizeCondo($expenseCategory->condominium_id);

        $condominiums = $this->getCondominiumsForSelect();
        return view('admin.expense-categories.edit', compact('expenseCategory', 'condominiums'));
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $this->authorizeCondo($expenseCategory->condominium_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ]);

        $expenseCategory->update($validated);

        return redirect()->route('expense-categories.index')
            ->with('success', __('messages.common.save') . '!');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        $this->authorizeCondo($expenseCategory->condominium_id);

        if ($expenseCategory->expenses()->count() > 0) {
            return back()->with('error', __('messages.expense_categories.has_expenses'));
        }

        $expenseCategory->delete();

        return redirect()->route('expense-categories.index')
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