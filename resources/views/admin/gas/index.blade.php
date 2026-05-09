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