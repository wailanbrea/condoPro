@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm">chevron_right</span>
    <a href="{{ route('expense-categories.index') }}" class="text-body-sm hover:text-primary transition-colors">{{ app()->getLocale() === 'es' ? 'Categorías' : 'Categories' }}</a>
    <span class="material-symbols-outlined text-sm">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ $expenseCategory->name }}</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">{{ $expenseCategory->name }}</h2>
        <p class="text-on-surface-variant">{{ $expenseCategory->condominium->name }}</p>
    </div>
    <div class="flex items-center gap-sm">
        <a href="{{ route('expense-categories.edit', $expenseCategory) }}" class="px-lg py-md bg-primary text-on-primary rounded-lg flex items-center gap-sm font-bold shadow-sm hover:bg-primary-container hover:text-on-primary-container transition-colors">
            <span class="material-symbols-outlined">edit</span>
            {{ app()->getLocale() === 'es' ? 'Editar' : 'Edit' }}
        </a>
        <a href="{{ route('expense-categories.index') }}" class="px-lg py-md bg-white border border-outline-variant rounded-lg flex items-center gap-sm hover:bg-surface-container-low transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
            {{ app()->getLocale() === 'es' ? 'Volver' : 'Back' }}
        </a>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
    <div class="p-lg">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-lg">
            <div class="bg-surface-container-low rounded-lg p-md border border-outline-variant">
                <p class="font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Nombre' : 'Name' }}</p>
                <p class="text-headline-md text-on-surface font-bold">{{ $expenseCategory->name }}</p>
            </div>
            <div class="bg-surface-container-low rounded-lg p-md border border-outline-variant">
                <p class="font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Condominio' : 'Condominium' }}</p>
                <p class="text-headline-md text-on-surface font-bold">{{ $expenseCategory->condominium->name }}</p>
            </div>
            <div class="bg-surface-container-low rounded-lg p-md border border-outline-variant">
                <p class="font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Estado' : 'Status' }}</p>
                @if($expenseCategory->status === 'active')
                    <span class="inline-flex items-center px-sm py-0.5 rounded bg-[#E3FCEF] text-[#006644] text-sm font-bold uppercase tracking-wider">{{ app()->getLocale() === 'es' ? 'Activa' : 'Active' }}</span>
                @else
                    <span class="inline-flex items-center px-sm py-0.5 rounded bg-red-100 text-red-800 text-sm font-bold uppercase tracking-wider">{{ app()->getLocale() === 'es' ? 'Inactiva' : 'Inactive' }}</span>
                @endif
            </div>
        </div>

        <div class="mt-lg">
            <h3 class="font-title-lg text-on-surface mb-md">{{ app()->getLocale() === 'es' ? 'Egresos asociados' : 'Associated expenses' }} ({{ $expenseCategory->expenses->count() }})</h3>
            @if($expenseCategory->expenses->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-surface-container-low/50">
                            <tr>
                                <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Fecha' : 'Date' }}</th>
                                <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Concepto' : 'Concept' }}</th>
                                <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant text-right">{{ app()->getLocale() === 'es' ? 'Monto' : 'Amount' }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            @foreach($expenseCategory->expenses->take(10) as $expense)
                                <tr class="hover:bg-surface-container-low transition-colors">
                                    <td class="px-md py-md text-on-surface-variant">{{ $expense->date?->format('d/m/Y') }}</td>
                                    <td class="px-md py-md text-on-surface">{{ $expense->concept }}</td>
                                    <td class="px-md py-md text-on-surface text-right font-mono-data font-bold">{{ number_format($expense->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'No hay egresos asociados a esta categoría.' : 'No expenses associated with this category.' }}</p>
            @endif
        </div>
    </div>
</div>
@endsection