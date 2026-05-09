@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm">chevron_right</span>
    <a href="{{ route('expense-categories.index') }}" class="text-body-sm hover:text-primary transition-colors">{{ app()->getLocale() === 'es' ? 'Categorías' : 'Categories' }}</a>
    <span class="material-symbols-outlined text-sm">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ app()->getLocale() === 'es' ? 'Nueva Categoría' : 'New Category' }}</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <h2 class="font-display-xl text-display-xl text-on-surface">{{ app()->getLocale() === 'es' ? 'Nueva Categoría de Egreso' : 'New Expense Category' }}</h2>
    <a href="{{ route('expense-categories.index') }}" class="px-lg py-md bg-white border border-outline-variant rounded-lg flex items-center gap-sm hover:bg-surface-container-low transition-colors">
        <span class="material-symbols-outlined">arrow_back</span>
        {{ __('messages.common.back_to_list') }}
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-lg max-w-2xl">
    <form action="{{ route('expense-categories.store') }}" method="POST">
        @csrf
        <div class="space-y-lg">
            <div>
                <label for="condominium_id" class="block font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Condominio' : 'Condominium' }} <span class="text-error">*</span></label>
                <select id="condominium_id" name="condominium_id" required class="w-full px-md py-sm border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all">
                    <option value="">{{ app()->getLocale() === 'es' ? 'Seleccionar condominio' : 'Select condominium' }}</option>
                    @foreach($condominiums as $id => $name)
                        <option value="{{ $id }}" {{ old('condominium_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @error('condominium_id') <p class="text-error text-body-sm mt-xs">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="name" class="block font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Nombre de la Categoría' : 'Category Name' }} <span class="text-error">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required class="w-full px-md py-sm border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all" placeholder="{{ app()->getLocale() === 'es' ? 'Ej: Nómina, Servicios...' : 'E.g., Payroll, Utilities...' }}">
                @error('name') <p class="text-error text-body-sm mt-xs">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="status" class="block font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Estado' : 'Status' }}</label>
                <select id="status" name="status" class="w-full px-md py-sm border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all">
                    <option value="active" selected>{{ app()->getLocale() === 'es' ? 'Activa' : 'Active' }}</option>
                    <option value="inactive">{{ app()->getLocale() === 'es' ? 'Inactiva' : 'Inactive' }}</option>
                </select>
            </div>

            <div class="flex items-center gap-md pt-sm border-t border-outline-variant/30">
                <button type="submit" class="px-lg py-md bg-primary text-on-primary rounded-lg flex items-center gap-sm font-bold shadow-sm hover:bg-primary-container hover:text-on-primary-container transition-colors">
                    <span class="material-symbols-outlined">save</span>
                    {{ app()->getLocale() === 'es' ? 'Guardar Categoría' : 'Save Category' }}
                </button>
                <a href="{{ route('expense-categories.index') }}" class="px-lg py-md bg-white border border-outline-variant rounded-lg flex items-center gap-sm hover:bg-surface-container-low transition-colors">
                    {{ __('messages.common.cancel') }}
                </a>
            </div>
        </div>
    </form>
</div>
@endsection