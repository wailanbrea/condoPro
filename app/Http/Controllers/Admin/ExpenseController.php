<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Condominium;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $expenses = Expense::with('category', 'creator', 'condominium')
            ->when($user->role === 'admin', fn($q) => $q->where('condominium_id', $user->condominium_id))
            ->orderBy('date', 'desc')
            ->get();

        return view('admin.expenses.index', compact('expenses'));
    }

    public function create(): View
    {
        $condominiums = $this->getCondominiumsForSelect();
        $categories = $this->getCategoriesForSelect();

        return view('admin.expenses.create', compact('condominiums', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'condominium_id' => 'required|exists:condominiums,id',
            'category_id' => 'required|exists:expense_categories,id',
            'date' => 'required|date',
            'concept' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'receipt_path' => 'nullable|string|max:500',
        ]);

        $this->authorizeCondo($validated['condominium_id']);

        $validated['created_by'] = Auth::id();

        Expense::create($validated);

        return redirect()->route('expenses.index')
            ->with('success', __('messages.common.save') . '!');
    }

    public function show(Expense $expense): View
    {
        $this->authorizeCondo($expense->condominium_id);

        $expense->load('category', 'creator', 'condominium');

        return view('admin.expenses.show', compact('expense'));
    }

    public function edit(Expense $expense): View
    {
        $this->authorizeCondo($expense->condominium_id);

        $condominiums = $this->getCondominiumsForSelect();
        $categories = $this->getCategoriesForSelect();

        return view('admin.expenses.edit', compact('expense', 'condominiums', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $this->authorizeCondo($expense->condominium_id);

        $validated = $request->validate([
            'condominium_id' => 'required|exists:condominiums,id',
            'category_id' => 'required|exists:expense_categories,id',
            'date' => 'required|date',
            'concept' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'receipt_path' => 'nullable|string|max:500',
        ]);

        $this->authorizeCondo($validated['condominium_id']);

        $expense->update($validated);

        return redirect()->route('expenses.show', $expense)
            ->with('success', __('messages.common.save') . '!');
    }

    public function destroy(Expense $expense)
    {
        $this->authorizeCondo($expense->condominium_id);

        $expense->delete();

        return redirect()->route('expenses.index')
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

    private function getCategoriesForSelect()
    {
        $user = Auth::user();
        if ($user->role === 'super_admin') {
            return ExpenseCategory::with('condominium')->orderBy('name')->get()->mapWithKeys(fn($c) => [$c->id => $c->condominium->name . ' - ' . $c->name]);
        }
        return ExpenseCategory::where('condominium_id', $user->condominium_id)->orderBy('name')->pluck('name', 'id');
    }
}