@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <a href="{{ route('extra-charges.index') }}" class="text-body-sm hover:text-primary transition-colors">{{ __('messages.nav.extra_fees') }}</a>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">Detalle</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">{{ $extra_charge->title }}</h2>
        <p class="text-on-surface-variant">Detalle de cuota extraordinaria</p>
    </div>
    <div class="flex gap-md">
        <a href="{{ route('extra-charges.edit', $extra_charge) }}" class="px-lg py-2 bg-primary text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all">
            <span class="material-symbols-outlined" data-icon="edit">edit</span>
            {{ __('messages.common.edit') }}
        </a>
        <a href="{{ route('extra-charges.index') }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
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
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">Monto Total</p>
        <h3 class="font-headline-lg text-headline-lg text-primary">RD${{ number_format($extra_charge->total_amount, 2) }}</h3>
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-secondary">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">Tipo de Reparto</p>
        @switch($extra_charge->distribution_type)
            @case('equal')
                <span class="px-3 py-1 bg-primary-fixed text-primary rounded-full text-[11px] font-bold uppercase tracking-wider">Igualitario</span>
                @break
            @case('selected')
                <span class="px-3 py-1 bg-tertiary-fixed text-tertiary rounded-full text-[11px] font-bold uppercase tracking-wider">Seleccionados</span>
                @break
            @case('percentage')
                <span class="px-3 py-1 bg-secondary-container text-on-secondary rounded-full text-[11px] font-bold uppercase tracking-wider">Porcentaje</span>
                @break
            @case('manual')
                <span class="px-3 py-1 bg-surface-container-low text-on-surface-variant rounded-full text-[11px] font-bold uppercase tracking-wider">Manual</span>
                @break
            @case('by_area')
                <span class="px-3 py-1 bg-secondary-container text-on-secondary rounded-full text-[11px] font-bold uppercase tracking-wider">Por Área</span>
                @break
            @case('installments')
                <span class="px-3 py-1 bg-tertiary-fixed text-tertiary rounded-full text-[11px] font-bold uppercase tracking-wider">Cuotas</span>
                @break
            @default
                <span class="px-3 py-1 bg-surface-container-low text-on-surface-variant rounded-full text-[11px] font-bold uppercase tracking-wider">{{ $extra_charge->distribution_type }}</span>
        @endswitch
        <p class="text-body-sm text-on-surface-variant mt-sm">Cuotas: {{ $extra_charge->installments_count }}</p>
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-outline">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.common.status') }}</p>
        @switch($extra_charge->status)
            @case('active')
                <span class="px-3 py-1 bg-[#E3FCEF] text-[#006644] rounded-full text-[11px] font-bold uppercase tracking-wider">{{ __('messages.common.active') }}</span>
                @break
            @case('completed')
                <span class="px-3 py-1 bg-secondary-container text-on-secondary rounded-full text-[11px] font-bold uppercase tracking-wider">Completado</span>
                @break
            @case('cancelled')
                <span class="px-3 py-1 bg-surface-container-low text-on-surface-variant rounded-full text-[11px] font-bold uppercase tracking-wider">{{ __('messages.common.cancelled') }}</span>
                @break
            @default
                <span class="px-3 py-1 bg-surface-container-low text-on-surface-variant rounded-full text-[11px] font-bold uppercase tracking-wider">{{ $extra_charge->status }}</span>
        @endswitch
        <p class="text-body-sm text-on-surface-variant mt-sm">Inicio: {{ \Carbon\Carbon::create()->month($extra_charge->start_month)->translatedFormat('F') }} {{ $extra_charge->start_year }}</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg mb-xl">
    <h4 class="font-headline-md text-headline-md mb-lg">Información General</h4>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
        <div>
            <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">Título</p>
            <p class="text-on-surface">{{ $extra_charge->title }}</p>
        </div>
        @if($extra_charge->description)
        <div>
            <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">Descripción</p>
            <p class="text-on-surface">{{ $extra_charge->description }}</p>
        </div>
        @endif
    </div>
</div>

<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] overflow-hidden mb-xl">
    <div class="p-lg border-b border-surface-container-low">
        <h4 class="font-headline-md text-headline-md">Apartamentos Asignados</h4>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-low/50">
                <tr>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.common.apartment') }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant text-right">Monto Asignado</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant text-right">Monto Mensual</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant text-right">Porcentaje</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-container-low">
                @forelse($extra_charge->extraChargeApartments as $eca)
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-lg py-md font-bold text-on-surface">{{ $eca->apartment->number ?? '—' }}</td>
                        <td class="px-lg py-md font-mono-data text-mono-data text-right">{{ number_format($eca->assigned_amount, 2) }}</td>
                        <td class="px-lg py-md font-mono-data text-mono-data text-right">{{ number_format($eca->monthly_amount, 2) }}</td>
                        <td class="px-lg py-md text-right">{{ number_format($eca->percentage, 2) }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-lg py-xl text-center text-on-surface-variant">{{ __('messages.common.no_results') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] overflow-hidden">
    <div class="p-lg border-b border-surface-container-low">
        <h4 class="font-headline-md text-headline-md">Cuotas</h4>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-low/50">
                <tr>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">Mes</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">Año</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant text-right">{{ __('messages.common.amount') }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.common.status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-container-low">
                @forelse($extra_charge->extraChargeInstallments as $installment)
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-lg py-md text-on-surface">{{ \Carbon\Carbon::create()->month($installment->billing_month)->translatedFormat('F') }}</td>
                        <td class="px-lg py-md text-on-surface-variant">{{ $installment->billing_year }}</td>
                        <td class="px-lg py-md font-mono-data text-mono-data text-right">{{ number_format($installment->amount, 2) }}</td>
                        <td class="px-lg py-md">
                            @switch($installment->status)
                                @case('pending')
                                    <span class="px-3 py-1 bg-tertiary-fixed text-tertiary rounded-full text-[11px] font-bold uppercase tracking-wider">{{ __('messages.common.pending') }}</span>
                                    @break
                                @case('billed')
                                    <span class="px-3 py-1 bg-secondary-container text-on-secondary rounded-full text-[11px] font-bold uppercase tracking-wider">Facturado</span>
                                    @break
                                @case('paid')
                                    <span class="px-3 py-1 bg-[#E3FCEF] text-[#006644] rounded-full text-[11px] font-bold uppercase tracking-wider">{{ __('messages.common.paid') }}</span>
                                    @break
                                @default
                                    <span class="px-3 py-1 bg-surface-container-low text-on-surface-variant rounded-full text-[11px] font-bold uppercase tracking-wider">{{ $installment->status }}</span>
                            @endswitch
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-lg py-xl text-center text-on-surface-variant">{{ __('messages.common.no_results') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection