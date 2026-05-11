@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ __('messages.nav.gas') }}</span>
</nav>

{{-- Header --}}
<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-md mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">Lecturas de Gas</h2>
        <p class="text-on-surface-variant text-body-md">Gestión de mediciones y facturación de gas por apartamento</p>
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

{{-- ============================================ --}}
{{-- TANK DASHBOARD --}}
{{-- ============================================ --}}
@if($tankData)
<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter mb-xl">
    {{-- Tank Gauge --}}
    <div class="lg:col-span-4">
        <div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg flex flex-col items-center justify-center relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-surface-container-low to-white opacity-40"></div>
            <h4 class="font-headline-md text-headline-md text-on-surface mb-xl relative z-10">{{ $setting->tank_name }}</h4>
            <div class="relative w-32 h-64 bg-slate-200 rounded-3xl border-4 border-slate-300 overflow-hidden z-10" style="box-shadow: inset 0 0 20px rgba(255,255,255,0.2), 0 0 30px rgba(0,61,155,0.1);">
                @php
                    $fillHeight = min(100, max(0, $tankData['percentage']));
                    $fillColor = $tankData['status'] === 'low' ? '#EF4444' : '#006644';
                    $fillColorEnd = $tankData['status'] === 'low' ? '#DC2626' : '#00823B';
                @endphp
                <div class="absolute bottom-0 left-0 right-0 transition-all duration-1000 ease-in-out"
                     style="height: {{ $fillHeight }}%; background: linear-gradient(to top, {{ $fillColorEnd }}, {{ $fillColor }}80, {{ $fillColor }}40);">
                    <div class="absolute top-0 left-0 right-0 h-4 overflow-hidden">
                        <svg viewBox="0 0 200 20" class="w-full" style="margin-top:-2px;">
                            <path d="M0,10 C25,18 50,2 100,10 C150,18 175,2 200,10 L200,20 L0,20 Z" fill="{{ $fillColor }}66" />
                            <path d="M0,12 C30,4 60,16 100,8 C140,0 170,14 200,8 L200,20 L0,20 Z" fill="{{ $fillColor }}44" />
                        </svg>
                    </div>
                </div>
                <div class="absolute inset-y-0 left-4 w-2 bg-white/10 rounded-full"></div>
                <div class="absolute inset-y-0 right-8 w-1 bg-white/5 rounded-full"></div>
            </div>
            <div class="mt-xl text-center z-10">
                <span class="text-[48px] font-black text-on-surface leading-tight">{{ number_format($tankData['percentage'], 0) }}%</span>
                <div class="flex items-center justify-center gap-xs mt-xs">
                    <span class="w-3 h-3 rounded-full {{ $tankData['status'] === 'low' ? 'bg-red-500' : 'bg-[#006644]' }}"></span>
                    <span class="{{ $tankData['status'] === 'low' ? 'text-red-700' : 'text-[#006644]' }} font-bold font-label-caps uppercase">{{ $tankData['statusLabel'] }}</span>
                </div>
                <p class="text-outline text-body-sm mt-md">{{ number_format($tankData['estimatedInventory'], 1) }} / {{ number_format($tankData['capacity'], 0) }} galones totales</p>
            </div>
            @if($tankData['status'] === 'low')
            <div class="mt-md w-full z-10 bg-red-50 border border-red-200 p-md rounded-lg flex gap-sm items-start">
                <span class="material-symbols-outlined text-red-600 text-[20px]">warning</span>
                <div><p class="text-red-800 font-bold text-body-sm">Nivel bajo. Se recomienda solicitar abastecimiento.</p></div>
            </div>
            @endif
        </div>
    </div>
    {{-- Metrics + Charts --}}
    <div class="lg:col-span-8 flex flex-col gap-gutter">
        {{-- Metric Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-md">
            <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-sm">
                <p class="text-outline font-label-caps mb-xs">GAS DISPONIBLE</p>
                <h4 class="text-headline-lg font-black text-on-surface">{{ number_format($tankData['estimatedInventory'], 1) }} <span class="text-body-lg font-normal text-outline">gal</span></h4>
                <div class="mt-md text-[#006644] text-body-sm flex items-center gap-xs">
                    <span class="material-symbols-outlined text-[18px]">water_drop</span>
                    de {{ number_format($tankData['capacity'], 0) }} galones
                </div>
            </div>
            <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-sm">
                <p class="text-outline font-label-caps mb-xs">CONSUMO MENSUAL</p>
                <h4 class="text-headline-lg font-black text-on-surface">{{ number_format($tankData['monthlyConsumption'], 1) }} <span class="text-body-lg font-normal text-outline">gal</span></h4>
                <div class="mt-md text-on-surface-variant text-body-sm flex items-center gap-xs">
                    <span class="material-symbols-outlined text-[18px]">equalizer</span>
                    Promedio: {{ number_format($tankData['dailyAverage'], 2) }} gal/día
                </div>
            </div>
            <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-sm">
                <p class="text-outline font-label-caps mb-xs">EST. RESTANTE</p>
                <h4 class="text-headline-lg font-black text-on-surface">{{ $tankData['estimatedDays'] }} <span class="text-body-lg font-normal text-outline">días</span></h4>
                <div class="mt-md {{ $tankData['status'] === 'low' ? 'text-red-600' : 'text-amber-600' }} font-bold text-body-sm flex items-center gap-xs">
                    <span class="material-symbols-outlined text-[18px]">event_repeat</event_repeat></span>
                    @if($tankData['lastDeliveryDate'])
                        Último: {{ $tankData['lastDeliveryDate'] }}
                    @else
                        Sin recepciones
                    @endif
                </div>
            </div>
        </div>
        {{-- Charts --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter">
            <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-sm">
                <h5 class="font-bold text-on-surface mb-lg">Consumo (6 meses)</h5>
                <div class="h-48 flex items-end justify-around gap-xs">
                    @php
                        $monthNames = [1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'];
                        $maxCons = $tankData['consumptionByMonth']->max('total_gallons') ?: 1;
                    @endphp
                    @foreach($tankData['consumptionByMonth'] as $monthData)
                        @php $barHeight = $maxCons > 0 ? ($monthData->total_gallons / $maxCons) * 100 : 0; $barHeight = max($barHeight, 5); @endphp
                        <div class="flex flex-col items-center gap-xs flex-1">
                            <div class="w-full bg-primary/20 rounded-t-md relative" style="height: {{ max($barHeight, 8) }}%;">
                                <div class="absolute bottom-0 left-0 right-0 bg-primary rounded-t-md" style="height: {{ $barHeight }}%;"></div>
                            </div>
                            <span class="text-[10px] text-outline">{{ $monthNames[$monthData->billing_month] ?? $monthData->billing_month }}</span>
                        </div>
                    @endforeach
                    @if($tankData['consumptionByMonth']->isEmpty())
                        <div class="text-center text-outline text-body-sm py-lg w-full">Sin datos de consumo</div>
                    @endif
                </div>
            </div>
            <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-sm">
                <h5 class="font-bold text-on-surface mb-lg">Compra vs Consumo</h5>
                <div class="h-48 flex items-end justify-around">
                    @php $maxVal = max($tankData['totalDelivered'], $tankData['totalConsumption'], 1); @endphp
                    <div class="flex flex-col items-center gap-sm">
                        <div class="w-16 bg-primary-container rounded-t-md" style="height: {{ $tankData['totalDelivered'] > 0 ? max(($tankData['totalDelivered'] / $maxVal) * 140, 20) : 10 }}px;"></div>
                        <span class="text-[11px] text-on-surface font-semibold">Compra</span>
                        <span class="text-[10px] text-outline">{{ number_format($tankData['totalDelivered'], 0) }} gal</span>
                    </div>
                    <div class="flex flex-col items-center gap-sm">
                        <div class="w-16 bg-[#006644] rounded-t-md" style="height: {{ $tankData['totalConsumption'] > 0 ? max(($tankData['totalConsumption'] / $maxVal) * 140, 20) : 10 }}px;"></div>
                        <span class="text-[11px] text-on-surface font-semibold">Consumo</span>
                        <span class="text-[10px] text-outline">{{ number_format($tankData['totalConsumption'], 0) }} gal</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ============================================ --}}
{{-- BLOQUE 1: CONFIGURACIÓN DEL PERÍODO --}}
{{-- ============================================ --}}
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg mb-lg">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-md mb-lg">
        <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary" data-icon="settings">settings</span>
            Configuración del Período
        </h3>
        <form method="GET" action="{{ route('gas.index') }}" class="flex gap-md items-end">
            @if(auth()->user()->role === 'super_admin')
                <div>
                    <label class="block text-label-caps text-on-surface-variant font-label-caps mb-1">Condominio</label>
                    <select name="condominium_id" class="px-md py-1.5 border border-outline-variant rounded-lg bg-surface-container-lowest text-sm text-on-surface">
                        @foreach($condominiums as $condo)
                            <option value="{{ $condo->id }}" {{ $condoId == $condo->id ? 'selected' : '' }}>{{ $condo->name }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="condominium_id" value="{{ $condoId }}">
            @endif
            <div>
                <label class="block text-label-caps text-on-surface-variant font-label-caps mb-1">Mes</label>
                <select name="month" class="px-md py-1.5 border border-outline-variant rounded-lg bg-surface-container-lowest text-sm text-on-surface">
                    @foreach($months as $m => $name)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-label-caps text-on-surface-variant font-label-caps mb-1">Año</label>
                <select name="year" class="px-md py-1.5 border border-outline-variant rounded-lg bg-surface-container-lowest text-sm text-on-surface">
                    @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="px-md py-1.5 bg-primary text-white rounded-lg text-sm flex items-center gap-1 hover:brightness-110 transition-all">
                <span class="material-symbols-outlined text-sm" data-icon="filter_list">filter_list</span>
                Filtrar
            </button>
        </form>
    </div>

    {{-- Config Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-md">
        <div class="bg-surface-container-low rounded-lg p-md border border-outline-variant">
            <p class="text-label-caps text-on-surface-variant font-label-caps mb-xs text-[11px]">Período</p>
            <p class="font-bold text-on-surface text-sm">{{ $months[$month] ?? $month }} {{ $year }}</p>
        </div>
        <div class="bg-surface-container-low rounded-lg p-md border border-outline-variant">
            <p class="text-label-caps text-on-surface-variant font-label-caps mb-xs text-[11px]">Rango de Lectura</p>
            <p class="font-mono-data text-on-surface font-bold text-sm">{{ $stats['config_start'] }} → {{ $stats['config_end'] }}</p>
        </div>
        <div class="bg-surface-container-low rounded-lg p-md border border-outline-variant">
            <p class="text-label-caps text-on-surface-variant font-label-caps mb-xs text-[11px]">Unidad</p>
            <p class="font-bold text-on-surface text-sm">m³ (Metros Cúbicos)</p>
        </div>
        <div class="bg-surface-container-low rounded-lg p-md border border-outline-variant">
            <p class="text-label-caps text-on-surface-variant font-label-caps mb-xs text-[11px]">Factor Conversión</p>
            <p class="font-mono-data text-on-surface font-bold text-sm">{{ number_format($stats['config_factor'], 4) }}</p>
        </div>
        <div class="bg-surface-container-low rounded-lg p-md border border-outline-variant">
            <p class="text-label-caps text-on-surface-variant font-label-caps mb-xs text-[11px]">Precio por Galón</p>
            <p class="font-mono-data text-on-surface font-bold text-sm">RD${{ number_format($stats['config_price'], 2) }}</p>
        </div>
        @if($stats['config_extra'] > 0)
        <div class="bg-surface-container-low rounded-lg p-md border border-outline-variant">
            <p class="text-label-caps text-on-surface-variant font-label-caps mb-xs text-[11px]">Costo Adicional</p>
            <p class="font-mono-data text-on-surface font-bold text-sm">RD${{ number_format($stats['config_extra'], 2) }}</p>
        </div>
        @endif
        <div class="bg-primary rounded-lg p-md">
            <p class="text-label-caps text-white/80 font-label-caps mb-xs text-[11px]">Precio Total por Galón</p>
            <p class="font-mono-data text-white font-bold text-lg">RD${{ number_format($stats['config_total_price'], 2) }}</p>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- BLOQUE 2: RESUMEN GENERAL --}}
{{-- ============================================ --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-md mb-lg">
    <div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-md border-l-4 border-primary">
        <p class="text-label-caps text-on-surface-variant font-label-caps text-[11px] mb-xs">Apartamentos</p>
        <p class="font-headline-lg text-headline-lg text-on-surface font-bold">{{ $stats['apartments_read'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-md border-l-4 border-secondary">
        <p class="text-label-caps text-on-surface-variant font-label-caps text-[11px] mb-xs">Consumo Total m³</p>
        <p class="font-mono-data text-headline-lg text-on-surface font-bold">{{ number_format($stats['total_consumption'], 3) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-md border-l-4 border-tertiary">
        <p class="text-label-caps text-on-surface-variant font-label-caps text-[11px] mb-xs">Total Galones</p>
        <p class="font-mono-data text-headline-lg text-on-surface font-bold">{{ number_format($stats['total_gallons'], 2) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-md border-l-4 border-primary">
        <p class="text-label-caps text-on-surface-variant font-label-caps text-[11px] mb-xs">Total Facturación</p>
        <p class="font-mono-data text-headline-lg text-primary font-bold">RD${{ number_format($stats['total_amount'], 2) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-md border-l-4 border-amber-500">
        <p class="text-label-caps text-on-surface-variant font-label-caps text-[11px] mb-xs">Pendientes</p>
        <p class="font-headline-lg text-headline-lg text-amber-600 font-bold">{{ $stats['pending_count'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-md border-l-4 border-[#006644]">
        <p class="text-label-caps text-on-surface-variant font-label-caps text-[11px] mb-xs">Facturados</p>
        <p class="font-headline-lg text-headline-lg text-[#006644] font-bold">{{ $stats['billed_count'] }}</p>
    </div>
</div>

{{-- ============================================ --}}
{{-- BLOQUE 3: FORMULARIO DE REGISTRO RÁPIDO --}}
{{-- ============================================ --}}
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg mb-lg border-t-4 border-primary">
    <h3 class="font-headline-md text-headline-md text-on-surface mb-lg flex items-center gap-2">
        <span class="material-symbols-outlined text-primary" data-icon="add_circle">add_circle</span>
        Registrar Lectura
    </h3>

    @if($errors->any())
        <div class="mb-lg px-md py-md bg-[#FFEBE6] text-[#BF2600] rounded-lg flex items-center gap-2 border border-[#BF2600]/20">
            <span class="material-symbols-outlined" data-icon="error">error</span>
            <ul class="list-disc list-inside text-body-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('gas.store') }}" method="POST" id="quickEntryForm">
        @csrf
        <input type="hidden" name="condominium_id" id="quick_condominium_id" value="{{ $condoId }}">
        <input type="hidden" name="billing_month" id="quick_billing_month" value="{{ $month }}">
        <input type="hidden" name="billing_year" id="quick_billing_year" value="{{ $year }}">
        <input type="hidden" name="reading_date_start" id="quick_reading_date_start" value="{{ now()->setDate($year, $month, 1)->format('Y-m-d') }}">
        <input type="hidden" name="reading_date_end" id="quick_reading_date_end" value="{{ now()->setDate($year, $month, 1)->endOfMonth()->format('Y-m-d') }}">
        <input type="hidden" name="conversion_factor" id="quick_conversion_factor" value="{{ $stats['config_factor'] }}">
        <input type="hidden" name="gallon_price" id="quick_gallon_price" value="{{ $stats['config_price'] }}">
        <input type="hidden" name="extra_cost_per_gallon" id="quick_extra_cost" value="{{ $stats['config_extra'] }}">
        <input type="hidden" name="price_per_gallon" id="quick_price_per_gallon" value="{{ $stats['config_price'] }}">

        <div class="grid grid-cols-1 md:grid-cols-6 gap-md items-end">
            <div>
                <label class="block text-label-caps text-on-surface-variant font-label-caps text-[11px] mb-1" for="quick_apartment_id">Apartamento <span class="text-error">*</span></label>
                <select id="quick_apartment_id" name="apartment_id" required class="w-full px-md py-2.5 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary-container focus:border-primary-container">
                    <option value="">Seleccionar</option>
                    @foreach(\App\Models\Apartment::where('condominium_id', $condoId)->where('status', 'active')->orderBy('number')->get() as $apt)
                        <option value="{{ $apt->id }}" data-meter="{{ $apt->gasReadings()->orderBy('id', 'desc')->value('meter_number') ?? '' }}">{{ $apt->number }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-label-caps text-on-surface-variant font-label-caps text-[11px] mb-1" for="quick_meter_number">No. Contador</label>
                <input type="text" id="quick_meter_number" name="meter_number" placeholder="Ej: G-001" class="w-full px-md py-2.5 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary-container focus:border-primary-container" />
            </div>
            <div>
                <label class="block text-label-caps text-on-surface-variant font-label-caps text-[11px] mb-1" for="quick_reading_initial">Lectura Anterior (m³) <span class="text-error">*</span></label>
                <input type="number" id="quick_reading_initial" name="reading_initial" step="0.001" min="0" required placeholder="0.000" class="w-full px-md py-2.5 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface text-sm text-right font-mono-data focus:ring-2 focus:ring-primary-container focus:border-primary-container" />
            </div>
            <div>
                <label class="block text-label-caps text-on-surface-variant font-label-caps text-[11px] mb-1" for="quick_reading_final">Lectura Actual (m³) <span class="text-error">*</span></label>
                <input type="number" id="quick_reading_final" name="reading_final" step="0.001" min="0" required placeholder="0.000" class="w-full px-md py-2.5 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface text-sm text-right font-mono-data focus:ring-2 focus:ring-primary-container focus:border-primary-container" />
            </div>
            <div>
                <label class="block text-label-caps text-on-surface-variant font-label-caps text-[11px] mb-1">Total a Facturar</label>
                <div id="quick_calc_total" class="w-full px-md py-2.5 border-2 border-primary rounded-lg bg-primary-container text-primary text-sm text-right font-mono-data font-bold">
                    RD$0.00
                </div>
            </div>
            <div>
                <button type="submit" class="w-full px-md py-2.5 bg-primary text-white rounded-lg flex items-center justify-center gap-2 hover:brightness-110 transition-all text-sm font-bold">
                    <span class="material-symbols-outlined" data-icon="save">save</span>
                    Registrar
                </button>
            </div>
        </div>

        <div id="quick_calc_details" class="hidden mt-md p-md bg-surface-container-low rounded-lg">
            <div class="grid grid-cols-4 gap-md text-center">
                <div>
                    <p class="text-label-caps text-on-surface-variant font-label-caps text-[10px]">Consumo m³</p>
                    <p id="quick_calc_consumption" class="font-mono-data text-on-surface font-bold">0.000</p>
                </div>
                <div>
                    <p class="text-label-caps text-on-surface-variant font-label-caps text-[10px]">Galones</p>
                    <p id="quick_calc_gallons" class="font-mono-data text-on-surface font-bold">0.00</p>
                </div>
                <div>
                    <p class="text-label-caps text-on-surface-variant font-label-caps text-[10px]">Precio/Galón</p>
                    <p id="quick_calc_price" class="font-mono-data text-on-surface font-bold">RD${{ number_format($stats['config_total_price'], 2) }}</p>
                </div>
                <div>
                    <p class="text-label-caps text-on-surface-variant font-label-caps text-[10px]">Fórmula</p>
                    <p id="quick_calc_formula" class="font-mono-data text-on-surface font-bold text-xs">—</p>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- ============================================ --}}
{{-- BLOQUE 4: LECTURAS DEL PERÍODO ACTUAL --}}
{{-- ============================================ --}}
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] overflow-hidden mb-lg">
    <div class="px-lg py-md bg-[#1B5E20] text-white flex items-center gap-2">
        <span class="material-symbols-outlined" data-icon="edit_note">edit_note</span>
        <h3 class="font-headline-md">LECTURAS DEL PERÍODO ACTUAL</h3>
        <span class="ml-auto bg-white/20 px-md py-0.5 rounded-full text-sm font-bold">{{ $currentReadings->count() }} registros</span>
    </div>

    @if($currentReadings->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#E8F5E9] border-b-2 border-[#1B5E20]">
                        <th class="px-md py-md text-label-caps font-label-caps text-[#1B5E20] text-[11px] whitespace-nowrap">Apto</th>
                        <th class="px-md py-md text-label-caps font-label-caps text-[#1B5E20] text-[11px] whitespace-nowrap">Contador</th>
                        <th class="px-md py-md text-label-caps font-label-caps text-[#1B5E20] text-[11px] whitespace-nowrap text-right">Lectura Anterior</th>
                        <th class="px-md py-md text-label-caps font-label-caps text-[#1B5E20] text-[11px] whitespace-nowrap text-right">Lectura Actual</th>
                        <th class="px-md py-md text-label-caps font-label-caps text-[#1B5E20] text-[11px] whitespace-nowrap text-right">Consumo m³</th>
                        <th class="px-md py-md text-label-caps font-label-caps text-[#1B5E20] text-[11px] whitespace-nowrap text-right">Galones</th>
                        <th class="px-md py-md text-label-caps font-label-caps text-[#1B5E20] text-[11px] whitespace-nowrap text-right">Precio Gal.</th>
                        <th class="px-md py-md text-label-caps font-label-caps text-[#1B5E20] text-[11px] whitespace-nowrap text-right">Total Gas</th>
                        <th class="px-md py-md text-label-caps font-label-caps text-[#1B5E20] text-[11px] whitespace-nowrap text-center">Estado</th>
                        <th class="px-md py-md text-label-caps font-label-caps text-[#1B5E20] text-[11px] whitespace-nowrap text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @foreach($currentReadings as $reading)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-md py-md font-bold text-on-surface text-sm">{{ $reading->apartment->number ?? '—' }}</td>
                            <td class="px-md py-md text-on-surface-variant text-sm">{{ $reading->meter_number ?? '—' }}</td>
                            <td class="px-md py-md font-mono-data text-on-surface-variant text-right text-sm">{{ number_format($reading->reading_initial, 3) }}</td>
                            <td class="px-md py-md font-mono-data text-on-surface text-right text-sm font-semibold">{{ number_format($reading->reading_final, 3) }}</td>
                            <td class="px-md py-md font-mono-data text-on-surface text-right text-sm font-bold">{{ number_format($reading->consumption_m3, 3) }}</td>
                            <td class="px-md py-md font-mono-data text-on-surface text-right text-sm">{{ number_format($reading->gallons, 2) }}</td>
                            <td class="px-md py-md font-mono-data text-on-surface-variant text-right text-xs">RD${{ number_format($reading->total_gallon_price, 2) }}</td>
                            <td class="px-md py-md font-mono-data text-primary text-right text-sm font-bold">RD${{ number_format($reading->total_amount, 2) }}</td>
                            <td class="px-md py-md text-center">
                                @if($reading->billed)
                                    <span class="px-2 py-1 bg-[#E3FCEF] text-[#006644] rounded-full text-[11px] font-bold uppercase tracking-wider">Facturado</span>
                                @else
                                    <span class="px-2 py-1 bg-amber-100 text-amber-800 rounded-full text-[11px] font-bold uppercase tracking-wider">Pendiente</span>
                                @endif
                            </td>
                            <td class="px-md py-md text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('gas.show', $reading) }}" class="p-1 hover:bg-surface-container-high rounded-lg text-on-surface-variant hover:text-primary transition-colors" title="Ver detalle">
                                        <span class="material-symbols-outlined text-lg" data-icon="visibility">visibility</span>
                                    </a>
                                    @if(!$reading->billed)
                                        <a href="{{ route('gas.edit', $reading) }}" class="p-1 hover:bg-surface-container-high rounded-lg text-on-surface-variant hover:text-primary transition-colors" title="Editar">
                                            <span class="material-symbols-outlined text-lg" data-icon="edit">edit</span>
                                        </a>
                                        <form action="{{ route('gas.destroy', $reading) }}" method="POST" onsubmit="return confirm('{{ __('messages.common.delete_confirm') }}')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 hover:bg-surface-container-high rounded-lg text-on-surface-variant hover:text-error transition-colors" title="Eliminar">
                                                <span class="material-symbols-outlined text-lg" data-icon="delete">delete</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-[#1B5E20] text-white">
                        <td class="px-md py-md font-label-caps text-white font-bold text-[11px]" colspan="4">TOTALES</td>
                        <td class="px-md py-md font-mono-data text-white text-right font-bold">{{ number_format($stats['total_consumption'], 3) }} m³</td>
                        <td class="px-md py-md font-mono-data text-white text-right font-bold">{{ number_format($stats['total_gallons'], 2) }} gal</td>
                        <td class="px-md py-md font-mono-data text-white/70 text-right text-xs">—</td>
                        <td class="px-md py-md font-mono-data text-white text-right font-bold text-base">RD${{ number_format($stats['total_amount'], 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @else
        <div class="py-xl text-center text-on-surface-variant">
            <span class="material-symbols-outlined text-5xl mb-sm block" data-icon="speed">speed</span>
            <p class="text-body-md">No hay lecturas registradas para este período</p>
            <p class="text-body-sm text-on-surface-variant mt-xs">Use el formulario de arriba para registrar la primera lectura</p>
        </div>
    @endif
</div>

{{-- ============================================ --}}
{{-- BLOQUE 5: HISTORIAL FACTURADO --}}
{{-- ============================================ --}}
@if($billedHistory->count() > 0)
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] overflow-hidden mb-lg">
    <button type="button" id="toggleHistory" class="w-full px-lg py-md bg-surface-container-low flex items-center justify-between hover:bg-surface-container transition-colors">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-on-surface-variant" data-icon="history">history</span>
            <h3 class="font-headline-md text-on-surface">HISTORIAL DE LECTURAS FACTURADAS</h3>
            <span class="bg-on-surface/10 px-md py-0.5 rounded-full text-sm font-bold text-on-surface-variant">{{ $billedHistory->count() }} registros</span>
        </div>
        <span class="material-symbols-outlined text-on-surface-variant transition-transform" id="historyArrow" data-icon="expand_more">expand_more</span>
    </button>

    <div id="historyContent" class="hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-low border-b-2 border-outline">
                        <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant text-[11px] whitespace-nowrap">Apto</th>
                        <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant text-[11px] whitespace-nowrap">Período</th>
                        <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant text-[11px] whitespace-nowrap">Contador</th>
                        <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant text-[11px] whitespace-nowrap text-right">Lectura Anterior</th>
                        <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant text-[11px] whitespace-nowrap text-right">Lectura Actual</th>
                        <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant text-[11px] whitespace-nowrap text-right">Consumo m³</th>
                        <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant text-[11px] whitespace-nowrap text-right">Galones</th>
                        <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant text-[11px] whitespace-nowrap text-right">Total</th>
                        <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant text-[11px] whitespace-nowrap text-center">Ver</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @foreach($billedHistory as $reading)
                        <tr class="hover:bg-surface-container-low transition-colors text-sm">
                            <td class="px-md py-md font-bold text-on-surface">{{ $reading->apartment->number ?? '—' }}</td>
                            <td class="px-md py-md text-on-surface-variant">{{ $months[$reading->billing_month] ?? $reading->billing_month }} {{ $reading->billing_year }}</td>
                            <td class="px-md py-md text-on-surface-variant">{{ $reading->meter_number ?? '—' }}</td>
                            <td class="px-md py-md font-mono-data text-on-surface-variant text-right text-xs">{{ number_format($reading->reading_initial, 3) }}</td>
                            <td class="px-md py-md font-mono-data text-on-surface text-right text-xs font-semibold">{{ number_format($reading->reading_final, 3) }}</td>
                            <td class="px-md py-md font-mono-data text-on-surface text-right">{{ number_format($reading->consumption_m3, 3) }}</td>
                            <td class="px-md py-md font-mono-data text-on-surface text-right">{{ number_format($reading->gallons, 2) }}</td>
                            <td class="px-md py-md font-mono-data text-primary text-right font-bold">RD${{ number_format($reading->total_amount, 2) }}</td>
                            <td class="px-md py-md text-center">
                                <a href="{{ route('gas.show', $reading) }}" class="p-1 hover:bg-surface-container-high rounded-lg text-on-surface-variant hover:text-primary transition-colors" title="Ver detalle">
                                    <span class="material-symbols-outlined text-lg" data-icon="visibility">visibility</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- ============================================ --}}
{{-- DELIVERY HISTORY --}}
{{-- ============================================ --}}
@if($setting && $deliveries->count() > 0)
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] overflow-hidden mb-xl">
    <div class="p-lg border-b border-outline-variant">
        <h4 class="font-headline-md text-headline-md flex items-center gap-2">
            <span class="material-symbols-outlined" data-icon="local_shipping">local_shipping</span>
            Historial de Recepciones de Gas
        </h4>
        <p class="text-on-surface-variant text-body-sm mt-xs">Registro de las veces que se ha comprado gas para el condominio</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-surface-container-low/80 border-b-2 border-primary">
                    <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant">Fecha</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant text-right">Lectura Antes</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant text-right">Lectura Después</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant text-right">Galones Recibidos</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant text-right">Monto Factura</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant">Estado</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deliveries as $delivery)
                <tr class="border-b border-outline-variant hover:bg-surface-container-lowest/50 transition-colors">
                    <td class="px-md py-md text-body-sm text-on-surface">{{ $delivery->delivery_date?->format('d/m/Y') ?? '—' }}</td>
                    <td class="px-md py-md text-body-sm text-on-surface text-right font-mono-data">{{ $delivery->tank_reading_before ? number_format($delivery->tank_reading_before, 3) : '—' }}</td>
                    <td class="px-md py-md text-body-sm text-on-surface text-right font-mono-data">{{ $delivery->tank_reading_after ? number_format($delivery->tank_reading_after, 3) : '—' }}</td>
                    <td class="px-md py-md text-body-sm text-on-surface text-right font-mono-data">{{ $delivery->gallons_delivered ? number_format($delivery->gallons_delivered, 2) . ' gal' : '—' }}</td>
                    <td class="px-md py-md text-body-sm text-on-surface text-right font-mono-data">{{ $delivery->invoice_amount ? 'RD$' . number_format($delivery->invoice_amount, 2) : '—' }}</td>
                    <td class="px-md py-md text-body-sm">
                        @if($delivery->status === 'completed')
                            <span class="px-3 py-1 bg-[#E3FCEF] text-[#006644] rounded-full text-[11px] font-bold uppercase tracking-wider">Completado</span>
                        @elseif($delivery->status === 'receiving')
                            <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-[11px] font-bold uppercase tracking-wider">Recibiendo</span>
                        @else
                            <span class="px-3 py-1 bg-[#E8F0FE] text-[#185ABC] rounded-full text-[11px] font-bold uppercase tracking-wider">Pendiente</span>
                        @endif
                    </td>
                    <td class="px-md py-md text-body-sm">
                        <a href="{{ route('gas-deliveries.show', $delivery) }}" class="text-primary hover:underline flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm" data-icon="visibility">visibility</span>
                            Ver
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-md py-md border-t border-outline-variant">
        {{ $deliveries->withQueryString()->links() }}
    </div>
</div>
@endif

{{-- ============================================ --}}
{{-- TANK SETTINGS --}}
{{-- ============================================ --}}
@if($setting)
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg mb-xl">
    <h4 class="font-headline-md text-headline-md mb-lg flex items-center gap-2">
        <span class="material-symbols-outlined" data-icon="settings">settings</span>
        Configuración del Tanque
    </h4>
    <form action="{{ route('gas-tank.update') }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="condominium_id" value="{{ $setting->condominium_id }}">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-lg mb-lg">
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-xs" for="tank_name">Nombre del Tanque <span class="text-error">*</span></label>
                <input type="text" id="tank_name" name="tank_name" value="{{ old('tank_name', $setting->tank_name) }}" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('tank_name') border-error ring-1 ring-error @enderror" />
                @error('tank_name')<p class="mt-1 text-body-sm text-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-xs" for="capacity_gallons">Capacidad Máxima (galones) <span class="text-error">*</span></label>
                <input type="number" id="capacity_gallons" name="capacity_gallons" value="{{ old('capacity_gallons', $setting->capacity_gallons) }}" step="0.01" min="1" required
                    class="w-full px-md py-md border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('capacity_gallons') border-error ring-1 ring-error @enderror" />
                @error('capacity_gallons')<p class="mt-1 text-body-sm text-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-xs" for="alert_min_gallons">Alerta Mínima (galones) <span class="text-error">*</span></label>
                <input type="number" id="alert_min_gallons" name="alert_min_gallons" value="{{ old('alert_min_gallons', $setting->alert_min_gallons) }}" step="0.01" min="0" required
                    class="w-full px-md py-md border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('alert_min_gallons') border-error ring-1 ring-error @enderror" />
                @error('alert_min_gallons')<p class="mt-1 text-body-sm text-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-xs" for="alert_min_percentage">Alerta Mínima (%) <span class="text-error">*</span></label>
                <input type="number" id="alert_min_percentage" name="alert_min_percentage" value="{{ old('alert_min_percentage', $setting->alert_min_percentage) }}" step="0.01" min="0" max="100" required
                    class="w-full px-md py-md border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('alert_min_percentage') border-error ring-1 ring-error @enderror" />
                @error('alert_min_percentage')<p class="mt-1 text-body-sm text-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-xs" for="average_consumption_method">Método Promedio de Consumo <span class="text-error">*</span></label>
                <select id="average_consumption_method" name="average_consumption_method" required
                    class="w-full px-md py-md border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('average_consumption_method') border-error ring-1 ring-error @enderror">
                    <option value="last_3_months" {{ old('average_consumption_method', $setting->average_consumption_method) === 'last_3_months' ? 'selected' : '' }}>Últimos 3 meses</option>
                    <option value="last_6_months" {{ old('average_consumption_method', $setting->average_consumption_method) === 'last_6_months' ? 'selected' : '' }}>Últimos 6 meses</option>
                    <option value="last_12_months" {{ old('average_consumption_method', $setting->average_consumption_method) === 'last_12_months' ? 'selected' : '' }}>Últimos 12 meses</option>
                </select>
                @error('average_consumption_method')<p class="mt-1 text-body-sm text-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-xs" for="status">Estado <span class="text-error">*</span></label>
                <select id="status" name="status" required
                    class="w-full px-md py-md border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('status') border-error ring-1 ring-error @enderror">
                    <option value="active" {{ old('status', $setting->status) === 'active' ? 'selected' : '' }}>Activo</option>
                    <option value="inactive" {{ old('status', $setting->status) === 'inactive' ? 'selected' : '' }}>Inactivo</option>
                </select>
                @error('status')<p class="mt-1 text-body-sm text-error">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex items-center gap-md mt-xl">
            <button type="submit" class="px-lg py-md bg-primary text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all">
                <span class="material-symbols-outlined" data-icon="save">save</span>
                Guardar
            </button>
            <button type="button" id="resetTankBtn" class="px-lg py-md bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
                <span class="material-symbols-outlined" data-icon="restart_alt">restart_alt</span>
                Restaurar Valores por Defecto
            </button>
        </div>
    </form>
</div>

{{-- Reset Confirmation --}}
<form id="resetTankForm" action="{{ route('gas-tank.reset') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="condominium_id" value="{{ $setting->condominium_id }}">
</form>
<div id="resetTankDialog" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-lg max-w-md w-full mx-4 shadow-2xl">
        <h3 class="font-headline-md text-headline-md text-on-surface mb-md flex items-center gap-2">
            <span class="material-symbols-outlined text-amber-500" data-icon="warning">warning</span>
            Restaurar Valores por Defecto
        </h3>
        <p class="text-body-md text-on-surface-variant mb-lg">
            Se restaurarán todos los valores a los predeterminados (Capacidad: 100 gal, Alerta: 20 gal / 20%, Método: últimos 3 meses). Esta acción no se puede deshacer.
        </p>
        <div class="flex justify-end gap-md">
            <button type="button" onclick="document.getElementById('resetTankDialog').classList.add('hidden')" class="px-lg py-md bg-white border border-outline-variant rounded-lg hover:bg-surface-container-low transition-colors">Cancelar</button>
            <button type="button" onclick="document.getElementById('resetTankForm').submit()" class="px-lg py-md bg-error text-white rounded-lg hover:brightness-110 transition-all">Restaurar</button>
        </div>
    </div>
</div>
@endif

@push('scripts')
@if($setting)
<script>
document.getElementById('resetTankBtn').addEventListener('click', function() {
    document.getElementById('resetTankDialog').classList.remove('hidden');
});
</script>
@endif
@endpush

@push('scripts')
<script>
(function() {
    // Toggle history
    const toggleBtn = document.getElementById('toggleHistory');
    const content = document.getElementById('historyContent');
    const arrow = document.getElementById('historyArrow');

    if (toggleBtn && content) {
        toggleBtn.addEventListener('click', function() {
            content.classList.toggle('hidden');
            arrow.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        });
    }

    // Quick entry form calculator
    const apartmentInput = document.getElementById('quick_apartment_id');
    const meterInput = document.getElementById('quick_meter_number');
    const initialInput = document.getElementById('quick_reading_initial');
    const finalInput = document.getElementById('quick_reading_final');
    const calcTotal = document.getElementById('quick_calc_total');
    const calcDetails = document.getElementById('quick_calc_details');
    const calcConsumption = document.getElementById('quick_calc_consumption');
    const calcGallons = document.getElementById('quick_calc_gallons');
    const calcFormula = document.getElementById('quick_calc_formula');

    const factor = parseFloat(document.getElementById('quick_conversion_factor').value) || 0;
    const price = parseFloat(document.getElementById('quick_gallon_price').value) || 0;
    const extra = parseFloat(document.getElementById('quick_extra_cost').value) || 0;
    const totalGallonPrice = price + extra;

    // Auto-fill meter number when apartment changes
    if (apartmentInput) {
        apartmentInput.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const meterVal = selected.getAttribute('data-meter') || '';
            meterInput.value = meterVal;
        });
    }

    function calculate() {
        const initial = parseFloat(initialInput.value) || 0;
        const final_ = parseFloat(finalInput.value) || 0;
        const consumption = final_ - initial;
        const gallons = consumption * factor;
        const total = gallons * totalGallonPrice;

        if (final_ > 0 || initial > 0) {
            calcDetails.classList.remove('hidden');
        }

        calcConsumption.textContent = Math.max(0, consumption).toFixed(3);
        calcGallons.textContent = Math.max(0, gallons).toFixed(2);
        calcTotal.textContent = 'RD$' + Math.max(0, total).toFixed(2);

        if (consumption > 0) {
            calcFormula.textContent = Math.max(0, consumption).toFixed(3) + ' m³ × ' + factor.toFixed(4) + ' = ' + Math.max(0, gallons).toFixed(2) + ' gal × RD$' + totalGallonPrice.toFixed(2);
        } else {
            calcFormula.textContent = '—';
        }

        // Highlight if final < initial
        if (final_ > 0 && initial > 0 && final_ < initial) {
            finalInput.classList.add('border-error');
            calcTotal.classList.remove('border-primary', 'bg-primary-container', 'text-primary');
            calcTotal.classList.add('border-error', 'bg-[#FFEBE6]', 'text-[#BF2600]');
            calcTotal.textContent = 'ERROR: Actual < Anterior';
        } else {
            finalInput.classList.remove('border-error');
            calcTotal.classList.remove('border-error', 'bg-[#FFEBE6]', 'text-[#BF2600]');
            calcTotal.classList.add('border-primary', 'bg-primary-container', 'text-primary');
        }
    }

    [initialInput, finalInput].forEach(function(el) {
        if (el) el.addEventListener('input', calculate);
    });
})();
</script>
@endpush>
@endsection