@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <a href="{{ route('gas.index') }}" class="text-body-sm hover:text-primary transition-colors">Gas</a>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">Inventario de Gas</span>
</nav>

<div class="flex flex-col md:flex-row md:items-end justify-between mb-xl gap-md">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">Inventario de Gas</h2>
        <p class="text-on-surface-variant">{{ now()->locale('es')->isoFormat('dddd, D [de] MMMM Y') }}</p>
    </div>
    <div class="flex gap-md">
        <a href="{{ route('gas-deliveries.index') }}" class="bg-white border border-outline-variant text-primary px-lg py-md rounded-lg font-semibold flex items-center gap-sm hover:bg-surface-container-low transition-colors">
            <span class="material-symbols-outlined" data-icon="local_shipping">local_shipping</span>
            Ver Recepciones
        </a>
        <a href="{{ route('gas-tank.edit') }}" class="bg-primary text-on-primary px-lg py-md rounded-lg font-semibold flex items-center gap-sm shadow-sm hover:brightness-110 transition-all active:scale-95">
            <span class="material-symbols-outlined" data-icon="settings">settings</span>
            Configurar Tanque
        </a>
    </div>
</div>

{{-- Condominium Selector --}}
@if(auth()->user()->role === 'super_admin')
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-md mb-xl">
    <form method="GET" action="{{ route('admin.gas.inventory') }}" class="flex items-end gap-md">
        <div class="flex-1">
            <label class="block font-label-caps text-label-caps text-on-surface-variant mb-xs" for="condominium_id">Condominio</label>
            <select name="condominium_id" id="condominium_id"
                class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all"
                onchange="this.form.submit();">
                @foreach($condominiums as $condo)
                    <option value="{{ $condo->id }}" {{ $condo->id == $condoId ? 'selected' : '' }}>{{ $condo->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-lg py-2 bg-primary text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all">
            <span class="material-symbols-outlined" data-icon="filter_list">filter_list</span>
            Filtrar
        </button>
    </form>
</div>
@endif

@if($tankData)
{{-- Bento Grid Layout --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">

    {{-- Tank Visualization --}}
    <div class="lg:col-span-4 flex flex-col gap-gutter">
        <div class="bg-white rounded-xl p-lg shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border border-outline-variant flex flex-col items-center justify-center relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-surface-container-low to-white opacity-40"></div>
            <h3 class="font-headline-md text-headline-md text-on-surface mb-xl relative z-10">{{ $setting->tank_name }}</h3>

            {{-- Vertical Tank Visual --}}
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
                {{-- Glass reflections --}}
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
        </div>

        {{-- Alert Card --}}
        @if($tankData['status'] === 'low')
        <div class="bg-red-50 border border-red-200 p-md rounded-xl flex gap-md items-start">
            <span class="material-symbols-outlined text-red-600">warning</span>
            <div>
                <p class="text-red-800 font-bold text-body-sm">Nivel de Gas Bajo</p>
                <p class="text-red-700 text-body-sm">Se recomienda solicitar abastecimiento. El nivel está por debajo de {{ number_format($setting->alert_min_gallons, 0) }} galones ({{ $setting->alert_min_percentage }}%).</p>
            </div>
        </div>
        @else
        <div class="bg-green-50 border border-green-200 p-md rounded-xl flex gap-md items-start">
            <span class="material-symbols-outlined text-green-600">check_circle</span>
            <div>
                <p class="text-green-800 font-bold text-body-sm">Configuración de Alertas</p>
                <p class="text-green-700 text-body-sm">Recibirás una notificación cuando el nivel baje del {{ $setting->alert_min_percentage }}% ({{ number_format($setting->alert_min_gallons, 0) }} galones).</p>
            </div>
        </div>
        @endif
    </div>

    {{-- Metrics & Charts --}}
    <div class="lg:col-span-8 flex flex-col gap-gutter">
        {{-- Metric Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-md">
            <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-sm">
                <p class="text-outline font-label-caps mb-xs">GAS DISPONIBLE</p>
                <h4 class="text-headline-lg font-black text-on-surface">{{ number_format($tankData['estimatedInventory'], 1) }} <span class="text-body-lg font-normal text-outline">gal</span></h4>
                <div class="mt-md text-on-surface-variant text-body-sm flex items-center gap-xs">
                    <span class="material-symbols-outlined text-[18px]">water_drop</span>
                    de {{ number_format($tankData['capacity'], 0) }} galones totales
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
                    <span class="material-symbols-outlined text-[18px]">event_repeat</span>
                    @if($tankData['lastDeliveryDate'])
                        Último: {{ $tankData['lastDeliveryDate'] }}
                    @else
                        Sin recepciones registradas
                    @endif
                </div>
            </div>
        </div>

        {{-- Consumption Chart --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter">
            <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-sm">
                <div class="flex justify-between items-center mb-lg">
                    <h5 class="font-bold text-on-surface">Consumo (6 meses)</h5>
                </div>
                <div class="h-48 flex items-end justify-around gap-xs">
                    @php
                        $monthNames = [1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'];
                        $maxConsumption = $tankData['consumptionByMonth']->max('total_gallons') ?: 1;
                    @endphp
                    @foreach($tankData['consumptionByMonth'] as $monthData)
                        @php
                            $barHeight = $maxConsumption > 0 ? ($monthData->total_gallons / $maxConsumption) * 100 : 0;
                            $barHeight = max($barHeight, 5);
                        @endphp
                        <div class="flex flex-col items-center gap-xs flex-1">
                            <div class="w-full bg-primary/20 rounded-t-md relative" style="height: {{ max($barHeight, 8) }}%;">
                                <div class="absolute bottom-0 left-0 right-0 bg-primary rounded-t-md" style="height: {{ $barHeight }}%;"></div>
                            </div>
                            <span class="text-[10px] text-outline">{{ $monthNames[$monthData->billing_month] ?? $monthData->billing_month }}</span>
                        </div>
                    @endforeach
                    @if($tankData['consumptionByMonth']->isEmpty())
                        <div class="text-center text-outline text-body-sm py-lg">Sin datos de consumo</div>
                    @endif
                </div>
            </div>

            <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-sm">
                <div class="flex justify-between items-center mb-lg">
                    <h5 class="font-bold text-on-surface">Compra vs Consumo</h5>
                </div>
                <div class="h-48 flex items-end justify-around">
                    @php
                        $totalDel = $tankData['totalDelivered'] ?: 0;
                        $totalCons = $tankData['totalConsumption'] ?: 0;
                        $maxVal = max($totalDel, $totalCons, 1);
                    @endphp
                    <div class="flex flex-col items-center gap-sm">
                        <div class="w-16 bg-primary-container rounded-t-md" style="height: {{ $totalDel > 0 ? max(($totalDel / $maxVal) * 140, 20) : 10 }}px;"></div>
                        <span class="text-[11px] text-on-surface font-semibold">Compra</span>
                        <span class="text-[10px] text-outline">{{ number_format($totalDel, 0) }} gal</span>
                    </div>
                    <div class="flex flex-col items-center gap-sm">
                        <div class="w-16 bg-[#006644] rounded-t-md" style="height: {{ $totalCons > 0 ? max(($totalCons / $maxVal) * 140, 20) : 10 }}px;"></div>
                        <span class="text-[11px] text-on-surface font-semibold">Consumo</span>
                        <span class="text-[10px] text-outline">{{ number_format($totalCons, 0) }} gal</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Supply History Table --}}
    <div class="lg:col-span-12">
        <div class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="px-lg py-md border-b border-outline-variant flex justify-between items-center bg-surface-container-low">
                <h5 class="font-bold text-on-surface">Historial de Abastecimientos</h5>
                <a href="{{ route('gas-deliveries.index') }}" class="text-primary text-body-sm font-semibold flex items-center gap-xs hover:underline">
                    <span class="material-symbols-outlined text-[18px]">list</span>
                    Ver todos
                </a>
            </div>

            @if($deliveries->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-on-surface-variant font-label-caps bg-surface-container-lowest">
                            <th class="px-lg py-md font-semibold">Fecha</th>
                            <th class="px-lg py-md font-semibold">Galones</th>
                            <th class="px-lg py-md font-semibold text-right">Lectura Antes</th>
                            <th class="px-lg py-md font-semibold text-right">Lectura Después</th>
                            <th class="px-lg py-md font-semibold text-right">Monto Factura</th>
                            <th class="px-lg py-md font-semibold text-center">Estado</th>
                            <th class="px-lg py-md font-semibold">Inventario Result.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @foreach($deliveries as $delivery)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-lg py-md font-mono-data text-on-surface">{{ $delivery->delivery_date?->format('d M Y') ?? '—' }}</td>
                            <td class="px-lg py-md text-right font-mono-data font-bold">{{ number_format($delivery->gallons_delivered ?? 0, 1) }} gal</td>
                            <td class="px-lg py-md text-right font-mono-data">{{ $delivery->tank_reading_before ? number_format($delivery->tank_reading_before, 1) : '—' }}</td>
                            <td class="px-lg py-md text-right font-mono-data">{{ $delivery->tank_reading_after ? number_format($delivery->tank_reading_after, 1) : '—' }}</td>
                            <td class="px-lg py-md text-right font-bold text-on-surface">{{ $delivery->invoice_amount ? 'RD$' . number_format($delivery->invoice_amount, 2) : '—' }}</td>
                            <td class="px-lg py-md text-center">
                                @if($delivery->status === 'completed')
                                    <span class="inline-flex items-center px-sm py-xs rounded-full bg-[#E3FCEF] text-[#006644] text-[11px] font-bold uppercase tracking-wider">Completado</span>
                                @elseif($delivery->status === 'receiving')
                                    <span class="inline-flex items-center px-sm py-xs rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold uppercase tracking-wider">Recibiendo</span>
                                @else
                                    <span class="inline-flex items-center px-sm py-xs rounded-full bg-[#E8F0FE] text-[#185ABC] text-[11px] font-bold uppercase tracking-wider">Pendiente</span>
                                @endif
                            </td>
                            <td class="px-lg py-md">
                                <div class="flex items-center gap-xs">
                                    <div class="w-24 h-2 bg-surface-container-high rounded-full overflow-hidden">
                                        @php
                                            $afterPct = $setting->capacity_gallons > 0 && $delivery->tank_reading_after
                                                ? min(100, ($delivery->tank_reading_after / $setting->capacity_gallons) * 100)
                                                : 0;
                                        @endphp
                                        <div class="bg-[#006644] h-full rounded-full" style="width: {{ $afterPct }}%;"></div>
                                    </div>
                                    <span class="text-[12px] text-outline">{{ $delivery->tank_reading_after ? number_format($delivery->tank_reading_after, 0) . ' gal' : '—' }}</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-lg flex justify-between items-center text-body-sm text-outline border-t border-outline-variant">
                <span>{{ $deliveries->total() }} recepciones</span>
                {{ $deliveries->withQueryString()->links() }}
            </div>
            @else
            <div class="p-xl text-center">
                <span class="material-symbols-outlined text-outline mb-md" data-icon="local_shipping" style="font-size:48px;"></span>
                <p class="text-on-surface-variant">No hay recepciones de gas registradas.</p>
                <p class="text-body-sm text-on-surface-variant mt-sm">Registre recepciones desde la app móvil para ver el historial.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@elseif($condoId)
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-xl text-center">
    <span class="material-symbols-outlined text-outline mb-md" data-icon="propane_tank" style="font-size:48px;"></span>
    <p class="text-on-surface-variant">No hay configuración de tanque para este condominio.</p>
    <a href="{{ route('gas-tank.edit', ['condominium_id' => $condoId]) }}" class="inline-flex items-center gap-sm mt-md text-primary font-semibold hover:underline">
        <span class="material-symbols-outlined">settings</span>
        Configurar Tanque
    </a>
</div>
@endif
@endsection