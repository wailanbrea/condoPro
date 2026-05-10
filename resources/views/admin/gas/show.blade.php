@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <a href="{{ route('gas.index') }}" class="text-body-sm hover:text-primary transition-colors">{{ __('messages.nav.gas') }}</a>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">Detalle de Lectura</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">Detalle de Lectura de Gas</h2>
        <p class="text-on-surface-variant">{{ $gas->apartment?->number ?? '—' }} — {{ $gas->condominium?->name ?? '—' }}</p>
    </div>
    <div class="flex gap-md">
        @if(!$gas->billed)
            <a href="{{ route('gas.edit', $gas) }}" class="px-lg py-2 bg-primary text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all">
                <span class="material-symbols-outlined" data-icon="edit">edit</span>
                {{ __('messages.common.edit') }}
            </a>
        @endif
        <a href="{{ route('gas.index') }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
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

{{-- Photo --}}
@if($gas->photo_path)
    <div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg mb-xl">
        <h4 class="font-headline-md text-headline-md mb-md flex items-center gap-2">
            <span class="material-symbols-outlined" data-icon="photo_camera">photo_camera</span>
            Foto del Contador
        </h4>
        <img src="{{ Storage::url($gas->photo_path) }}" alt="Foto del contador" class="max-h-80 rounded-lg object-contain">
    </div>
@endif

{{-- Status Badges --}}
<div class="grid grid-cols-1 lg:grid-cols-4 gap-gutter mb-xl">
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-primary">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.common.apartment') }}</p>
        <h3 class="font-headline-md text-headline-md text-on-surface">{{ $gas->apartment?->number ?? '—' }}</h3>
        <p class="text-body-sm text-on-surface-variant">{{ $gas->apartment?->owner_name ?? '—' }}</p>
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-secondary">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">Contador</p>
        <h3 class="font-headline-md text-headline-md text-on-surface">{{ $gas->meter_number ?? '—' }}</h3>
        <p class="text-body-sm text-on-surface-variant">{{ $gas->condominium?->name ?? '—' }}</p>
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-tertiary">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">Período</p>
        <h3 class="font-headline-md text-headline-md text-on-surface">{{ $gas->reading_date_start?->format('d/m/Y') ?? '—' }} — {{ $gas->reading_date_end?->format('d/m/Y') ?? '—' }}</h3>
        <p class="text-body-sm text-on-surface-variant">{{ $gas->billing_month ? ucfirst(\Carbon\Carbon::create()->month($gas->billing_month)->locale('es')->monthName) : '—' }} {{ $gas->billing_year ?? '—' }}</p>
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 {{ $gas->billed ? 'border-[#006644]' : 'border-amber-500' }}">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.common.status') }}</p>
        @if($gas->billed)
            <span class="px-3 py-1 bg-[#E3FCEF] text-[#006644] rounded-full text-[11px] font-bold uppercase tracking-wider">Facturado</span>
        @else
            <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-[11px] font-bold uppercase tracking-wider">Pendiente</span>
        @endif
        <p class="text-body-sm text-on-surface-variant mt-sm">Creado por: {{ $gas->creator?->name ?? '—' }}</p>
    </div>
</div>

{{-- Calculation Breakdown Table --}}
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg mb-xl">
    <h4 class="font-headline-md text-headline-md mb-lg flex items-center gap-2">
        <span class="material-symbols-outlined" data-icon="calculate">calculate</span>
        Desglose del Cálculo
    </h4>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-surface-container-low/80 border-b-2 border-primary">
                    <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant">Paso</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant">Descripción</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant text-right">Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b border-outline-variant">
                    <td class="px-md py-md text-body-sm text-on-surface-variant">1</td>
                    <td class="px-md py-md text-body-sm text-on-surface">Lectura Actual</td>
                    <td class="px-md py-md font-mono-data text-on-surface text-right">{{ number_format($gas->reading_final ?? 0, 3) }} m³</td>
                </tr>
                <tr class="border-b border-outline-variant">
                    <td class="px-md py-md text-body-sm text-on-surface-variant">2</td>
                    <td class="px-md py-md text-body-sm text-on-surface">Lectura Anterior</td>
                    <td class="px-md py-md font-mono-data text-on-surface text-right">{{ number_format($gas->reading_initial ?? 0, 3) }} m³</td>
                </tr>
                <tr class="border-b border-outline-variant bg-surface-container-lowest">
                    <td class="px-md py-md text-body-sm text-on-surface-variant font-bold">3</td>
                    <td class="px-md py-md text-body-sm text-on-surface font-bold">Consumo en m³ (Final − Inicial)</td>
                    <td class="px-md py-md font-mono-data text-on-surface text-right font-bold">{{ number_format($gas->consumption_m3 ?? 0, 3) }} m³</td>
                </tr>
                <tr class="border-b border-outline-variant">
                    <td class="px-md py-md text-body-sm text-on-surface-variant">4</td>
                    <td class="px-md py-md text-body-sm text-on-surface">× Factor de Conversión</td>
                    <td class="px-md py-md font-mono-data text-on-surface text-right">{{ number_format($gas->conversion_factor ?? 0, 4) }}</td>
                </tr>
                <tr class="border-b border-outline-variant bg-surface-container-lowest">
                    <td class="px-md py-md text-body-sm text-on-surface-variant font-bold">5</td>
                    <td class="px-md py-md text-body-sm text-on-surface font-bold">Galones (Consumo × Factor)</td>
                    <td class="px-md py-md font-mono-data text-on-surface text-right font-bold">{{ number_format($gas->gallons ?? 0, 2) }} gal</td>
                </tr>
                <tr class="border-b border-outline-variant">
                    <td class="px-md py-md text-body-sm text-on-surface-variant">6</td>
                    <td class="px-md py-md text-body-sm text-on-surface">Precio por Galón</td>
                    <td class="px-md py-md font-mono-data text-on-surface text-right">RD${{ number_format($gas->gallon_price ?? 0, 2) }}</td>
                </tr>
                @if(($gas->extra_cost_per_gallon ?? 0) > 0)
                    <tr class="border-b border-outline-variant">
                        <td class="px-md py-md text-body-sm text-on-surface-variant">7</td>
                        <td class="px-md py-md text-body-sm text-on-surface">+ Costo Adicional por Galón</td>
                        <td class="px-md py-md font-mono-data text-on-surface text-right">RD${{ number_format($gas->extra_cost_per_gallon, 2) }}</td>
                    </tr>
                    <tr class="border-b border-outline-variant bg-surface-container-lowest">
                        <td class="px-md py-md text-body-sm text-on-surface-variant font-bold">8</td>
                        <td class="px-md py-md text-body-sm text-on-surface font-bold">= Precio Total por Galón</td>
                        <td class="px-md py-md font-mono-data text-on-surface text-right font-bold">RD${{ number_format($gas->total_gallon_price ?? 0, 2) }}</td>
                    </tr>
                @else
                    <tr class="border-b border-outline-variant bg-surface-container-lowest">
                        <td class="px-md py-md text-body-sm text-on-surface-variant font-bold">7</td>
                        <td class="px-md py-md text-body-sm text-on-surface font-bold">Precio Total por Galón</td>
                        <td class="px-md py-md font-mono-data text-on-surface text-right font-bold">RD${{ number_format($gas->total_gallon_price ?? $gas->gallon_price ?? 0, 2) }}</td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr class="bg-primary border-t-2 border-primary">
                    <td class="px-md py-md text-white font-bold" colspan="2" style="color: white !important;">TOTAL A FACTURAR ({{ number_format($gas->gallons ?? 0, 2) }} gal × RD${{ number_format($gas->total_gallon_price ?? $gas->gallon_price ?? 0, 2) }})</td>
                    <td class="px-md py-md font-mono-data text-headline-lg font-bold text-right" style="color: white !important;">RD${{ number_format($gas->total_amount ?? 0, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Formula Breakdown --}}
<div class="bg-[#E8F0FE] rounded-xl p-lg mb-xl">
    <div class="flex items-start gap-md">
        <span class="material-symbols-outlined text-primary mt-0.5" data-icon="function">function</span>
        <div>
            <p class="font-bold text-on-surface mb-xs">Fórmula Aplicada</p>
            <p class="font-mono-data text-on-surface">
                {{ number_format($gas->consumption_m3 ?? 0, 3) }} m³ × {{ number_format($gas->conversion_factor ?? 0, 4) }} = {{ number_format($gas->gallons ?? 0, 2) }} gal × RD${{ number_format($gas->total_gallon_price ?? $gas->gallon_price ?? 0, 2) }} = <strong>RD${{ number_format($gas->total_amount ?? 0, 2) }}</strong>
            </p>
        </div>
    </div>
</div>
@endsection