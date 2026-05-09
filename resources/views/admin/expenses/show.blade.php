@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <a href="{{ route('expenses.index') }}" class="text-body-sm hover:text-primary transition-colors">{{ __('messages.nav.expenses') }}</a>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ __('messages.common.details') }}</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">{{ $expense->concept }}</h2>
        <p class="text-on-surface-variant">{{ __('messages.common.details') }}</p>
    </div>
    <div class="flex gap-md">
        <a href="{{ route('expenses.edit', $expense) }}" class="px-lg py-2 bg-primary text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all">
            <span class="material-symbols-outlined" data-icon="edit">edit</span>
            {{ __('messages.common.edit') }}
        </a>
        <a href="{{ route('expenses.index') }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
            <span class="material-symbols-outlined" data-icon="arrow_back">arrow_back</span>
            {{ __('messages.common.back_to_list') }}
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-lg px-lg py-md bg-[#E3FCEF] text-[#006644] rounded-lg flex items-center gap-2 border border-[#006644]/20">
        <span class="material-symbols-outlined" data-icon="check_circle">check_circle</span>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-lg px-lg py-md bg-[#FFEBE6] text-[#BF2600] rounded-lg flex items-center gap-2 border border-[#BF2600]/20">
        <span class="material-symbols-outlined" data-icon="error">error</span>
        {{ session('error') }}
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter mb-xl">
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-primary">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.common.amount') }}</p>
        <h3 class="font-headline-lg text-headline-lg text-primary">{{ number_format($expense->amount, 2) }}</h3>
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-secondary">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.common.date') }}</p>
        <h3 class="font-headline-md text-headline-md text-secondary">{{ $expense->date ? \Carbon\Carbon::parse($expense->date)->format('d/m/Y') : '—' }}</h3>
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-outline">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.condominiums.title') }}</p>
        <h3 class="font-headline-md text-headline-md">{{ $expense->condominium->name ?? '—' }}</h3>
    </div>
</div>

<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg mb-xl">
    <h4 class="font-headline-md text-headline-md mb-lg">{{ __('messages.common.details') }}</h4>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
        <div>
            <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.common.concept') }}</p>
            <p class="text-on-surface">{{ $expense->concept }}</p>
        </div>
        <div>
            <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">Categoría</p>
            <p class="text-on-surface">{{ $expense->category->name ?? '—' }}</p>
        </div>
        @if($expense->receipt_path)
        <div>
            <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">Comprobante</p>
            <a href="{{ \Storage::url($expense->receipt_path) }}" target="_blank" class="text-primary hover:underline">{{ basename($expense->receipt_path) }}</a>
        </div>
        @endif
        <div>
            <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.common.created_by') ?? 'Creado por' }}</p>
            <p class="text-on-surface">{{ $expense->creator->name ?? '—' }}</p>
        </div>
    </div>
</div>
@endsection