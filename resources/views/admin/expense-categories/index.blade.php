@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ app()->getLocale() === 'es' ? 'Categorías de Egresos' : 'Expense Categories' }}</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">{{ app()->getLocale() === 'es' ? 'Categorías de Egresos' : 'Expense Categories' }}</h2>
        <p class="text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Administra las categorías para clasificar egresos' : 'Manage categories to classify expenses' }}</p>
    </div>
    <a href="{{ route('expense-categories.create') }}" class="px-lg py-md bg-primary text-on-primary rounded-lg flex items-center gap-sm font-bold shadow-sm hover:bg-primary-container hover:text-on-primary-container transition-colors">
        <span class="material-symbols-outlined">add</span>
        {{ app()->getLocale() === 'es' ? 'Nueva Categoría' : 'New Category' }}
    </a>
</div>

@if(session('success'))
    <div class="mb-lg px-lg py-md bg-[#E3FCEF] text-[#006644] rounded-lg flex items-center gap-2 border border-[#006644]/20">
        <span class="material-symbols-outlined">check_circle</span>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-lg px-lg py-md bg-[#FFEBE6] text-[#BF2600] rounded-lg flex items-center gap-2 border border-[#BF2600]/20">
        <span class="material-symbols-outlined">error</span>
        {{ session('error') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-low/50">
                <tr>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Nombre' : 'Name' }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Condominio' : 'Condominium' }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Egresos' : 'Expenses' }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Estado' : 'Status' }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Acciones' : 'Actions' }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @forelse($categories as $category)
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-lg py-md text-on-surface font-medium">{{ $category->name }}</td>
                        <td class="px-lg py-md text-on-surface-variant">{{ $category->condominium->name }}</td>
                        <td class="px-lg py-md text-on-surface-variant">{{ $category->expenses->count() }}</td>
                        <td class="px-lg py-md">
                            @if($category->status === 'active')
                                <span class="inline-flex items-center px-sm py-0.5 rounded bg-[#E3FCEF] text-[#006644] text-[11px] font-bold uppercase tracking-wider">{{ app()->getLocale() === 'es' ? 'Activa' : 'Active' }}</span>
                            @else
                                <span class="inline-flex items-center px-sm py-0.5 rounded bg-red-100 text-red-800 text-[11px] font-bold uppercase tracking-wider">{{ app()->getLocale() === 'es' ? 'Inactiva' : 'Inactive' }}</span>
                            @endif
                        </td>
                        <td class="px-lg py-md">
                            <div class="flex items-center gap-sm">
                                <a href="{{ route('expense-categories.show', $category) }}" class="p-sm rounded-lg hover:bg-surface-container-high transition-colors text-on-surface-variant hover:text-primary" title="{{ app()->getLocale() === 'es' ? 'Ver' : 'View' }}">
                                    <span class="material-symbols-outlined">visibility</span>
                                </a>
                                <a href="{{ route('expense-categories.edit', $category) }}" class="p-sm rounded-lg hover:bg-surface-container-high transition-colors text-on-surface-variant hover:text-primary" title="{{ app()->getLocale() === 'es' ? 'Editar' : 'Edit' }}">
                                    <span class="material-symbols-outlined">edit</span>
                                </a>
                                <form action="{{ route('expense-categories.destroy', $category) }}" method="POST" onsubmit="return confirm('{{ app()->getLocale() === 'es' ? '¿Eliminar esta categoría?' : 'Delete this category?' }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-sm rounded-lg hover:bg-red-50 transition-colors text-on-surface-variant hover:text-error" title="{{ app()->getLocale() === 'es' ? 'Eliminar' : 'Delete' }}">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-lg py-xl text-center text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'No hay categorías de egresos.' : 'No expense categories found.' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-lg">{{ $categories->links() }}</div>
@endsection