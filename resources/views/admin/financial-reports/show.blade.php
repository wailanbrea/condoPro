@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <a href="{{ route('financial-reports.index') }}" class="text-body-sm hover:text-primary transition-colors">Informe Financiero</a>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ $report->period_name }}</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">Informe Financiero - {{ $report->period_name }}</h2>
        <p class="text-on-surface-variant">{{ $report->condominium->name }}</p>
    </div>
    <div class="flex gap-md">
        @if($report->status === 'open')
            <form action="{{ route('financial-reports.close', $report) }}" method="POST" onsubmit="return confirm('¿Está seguro de cerrar este informe? No podrá modificarlo después.')">
                @csrf
                <button type="submit" class="px-lg py-2 bg-amber-500 text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all">
                    <span class="material-symbols-outlined" data-icon="lock">lock</span>
                    Cerrar Informe
                </button>
            </form>
        @else
            <span class="px-lg py-2 bg-[#E3FCEF] text-[#006644] rounded-lg flex items-center gap-2">
                <span class="material-symbols-outlined" data-icon="check_circle">check_circle</span>
                Informe Cerrado
            </span>
        @endif
        <a href="{{ route('financial-reports.index') }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
            <span class="material-symbols-outlined" data-icon="arrow_back">arrow_back</span>
            Volver
        </a>
    </div>
</div>

{{-- Status Bar --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-gutter mb-xl">
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-primary">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">Estado</p>
        @if($report->status === 'open')
            <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-[11px] font-bold uppercase tracking-wider">Abierto</span>
        @else
            <span class="px-3 py-1 bg-[#E3FCEF] text-[#006644] rounded-full text-[11px] font-bold uppercase tracking-wider">Cerrado</span>
        @endif
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-secondary">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">Creado por</p>
        <p class="text-on-surface font-bold">{{ $report->creator?->name ?? '—' }}</p>
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-outline">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">Total Ingresos</p>
        <p class="font-mono-data text-headline-md text-[#1B5E20] font-bold">RD${{ number_format($report->total_income, 2) }}</p>
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-[#B71C1C]">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">Total Gastos</p>
        <p class="font-mono-data text-headline-md text-[#B71C1C] font-bold">RD${{ number_format($report->total_expenses, 2) }}</p>
    </div>
</div>

{{-- Financial Summary --}}
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] overflow-hidden mb-lg border-l-4 border-primary">
    <div class="px-lg py-md bg-primary text-white flex items-center gap-2">
        <span class="material-symbols-outlined" data-icon="account_balance">account_balance</span>
        <h3 class="font-headline-md">BALANCE GENERAL</h3>
    </div>
    <div class="p-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-lg">
            <div class="bg-surface-container-low rounded-lg p-md border border-outline-variant">
                <p class="font-label-caps text-label-caps text-on-surface-variant mb-xs">Balance Inicial</p>
                <p class="font-mono-data text-headline-lg text-on-surface font-bold">{{ number_format($report->initial_balance, 2) }}</p>
            </div>
            <div class="bg-[#E8F5E9] rounded-lg p-md border border-[#1B5E20]/20">
                <p class="font-label-caps text-label-caps text-[#1B5E20] mb-xs">+ Ingresos</p>
                <p class="font-mono-data text-headline-lg text-[#1B5E20] font-bold">RD${{ number_format($report->total_income, 2) }}</p>
            </div>
            <div class="bg-[#FFEBEE] rounded-lg p-md border border-[#B71C1C]/20">
                <p class="font-label-caps text-label-caps text-[#B71C1C] mb-xs">− Gastos</p>
                <p class="font-mono-data text-headline-lg text-[#B71C1C] font-bold">RD${{ number_format($report->total_expenses, 2) }}</p>
            </div>
            <div class="bg-[#FFF3E0] rounded-lg p-md border border-amber-500/20">
                <p class="font-label-caps text-label-caps text-amber-800 mb-xs">− Pagos Especiales</p>
                <p class="font-mono-data text-headline-lg text-amber-800 font-bold">RD${{ number_format($report->special_payments, 2) }}</p>
            </div>
            <div class="bg-surface-container-low rounded-lg p-md border border-outline-variant">
                <p class="font-label-caps text-label-caps text-on-surface-variant mb-xs">Mantenimiento</p>
                <p class="font-mono-data text-headline-md text-on-surface font-bold">RD${{ number_format($report->total_maintenance, 2) }}</p>
            </div>
            <div class="bg-surface-container-low rounded-lg p-md border border-outline-variant">
                <p class="font-label-caps text-label-caps text-on-surface-variant mb-xs">Gas</p>
                <p class="font-mono-data text-headline-md text-on-surface font-bold">RD${{ number_format($report->total_gas, 2) }}</p>
            </div>
            <div class="bg-surface-container-low rounded-lg p-md border border-outline-variant">
                <p class="font-label-caps text-label-caps text-on-surface-variant mb-xs">Cuotas Extraordinarias</p>
                <p class="font-mono-data text-headline-md text-on-surface font-bold">RD${{ number_format($report->total_extra_charges, 2) }}</p>
            </div>
            <div class="bg-surface-container-low rounded-lg p-md border border-outline-variant">
                <p class="font-label-caps text-label-caps text-on-surface-variant mb-xs">Pendiente</p>
                <p class="font-mono-data text-headline-md text-amber-800 font-bold">RD${{ number_format($report->total_pending, 2) }}</p>
            </div>
            <div class="bg-primary rounded-lg p-md border-2 border-primary">
                <p class="font-label-caps text-white/80 mb-xs">BALANCE FINAL</p>
                <p class="font-mono-data text-headline-lg text-white font-bold">RD${{ number_format($report->final_balance, 2) }}</p>
            </div>
        </div>

        @if($report->notes)
            <div class="mt-lg p-md bg-surface-container-low rounded-lg">
                <p class="font-bold text-on-surface mb-xs">Notas:</p>
                <p class="text-on-surface-variant">{{ $report->notes }}</p>
            </div>
        @endif

        <div class="mt-lg text-body-sm text-on-surface-variant">
            @if($report->closed_at)
                <p>Cerrado por: {{ $report->closer?->name ?? '—' }} el {{ $report->closed_at->format('d/m/Y H:i') }}</p>
            @endif
            <p>Creado por: {{ $report->creator?->name ?? '—' }} el {{ $report->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</div>
@endsection