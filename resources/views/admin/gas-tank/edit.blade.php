@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <a href="{{ route('gas.index') }}" class="text-body-sm hover:text-primary transition-colors">Gas</a>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">Tanque Principal</span>
</nav>

<div class="flex flex-col md:flex-row md:items-end justify-between mb-xl gap-md">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">Tanque Principal</h2>
        <p class="text-on-surface-variant">Inventario, gráficas y configuración del gas</p>
    </div>
    <div class="flex gap-md">
        <a href="{{ route('gas-deliveries.index') }}" class="bg-white border border-outline-variant text-primary px-lg py-md rounded-lg font-semibold flex items-center gap-sm hover:bg-surface-container-low transition-colors">
            <span class="material-symbols-outlined" data-icon="local_shipping">local_shipping</span>
            Recepciones
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-lg px-lg py-md bg-[#E3FCEF] text-[#006644] rounded-lg flex items-center gap-2 border border-[#006644]/20">
        <span class="material-symbols-outlined" data-icon="check_circle">check_circle</span>
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-lg px-lg py-md bg-[#FFEBE6] text-[#BF2600] rounded-lg border border-[#BF2600]/20">
        <div class="flex items-center gap-2 mb-sm">
            <span class="material-symbols-outlined" data-icon="error">error</span>
            <span class="font-bold">Corrija los errores:</span>
        </div>
        <ul class="list-disc list-inside text-body-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Condominium Selector --}}
@if(auth()->user()->role === 'super_admin')
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg mb-xl">
    <form method="GET" action="{{ route('gas-tank.edit') }}" id="condoSelector">
        <label class="block font-label-caps text-label-caps text-on-surface-variant mb-xs" for="condominium_id">Condominio</label>
        <select name="condominium_id" id="condominium_id"
            class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all"
            onchange="document.getElementById('condoSelector').submit();">
            @foreach($condominiums as $condo)
                <option value="{{ $condo->id }}" {{ $condo->id == $condoId ? 'selected' : '' }}>{{ $condo->name }}</option>
            @endforeach
        </select>
    </form>
</div>
@endif

@if($condoId && $setting && $tankData)

{{-- TANK GAUGE + METRICS --}}
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
                <div>
                    <p class="text-red-800 font-bold text-body-sm">Nivel bajo. Se recomienda solicitar abastecimiento.</p>
                </div>
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
            {{-- Consumption Chart --}}
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
            {{-- Compra vs Consumo --}}
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

{{-- Supply History --}}
@if($deliveries->count() > 0)
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg mb-xl">
    <div class="flex justify-between items-center mb-lg">
        <h4 class="font-headline-md text-headline-md flex items-center gap-2">
            <span class="material-symbols-outlined" data-icon="local_shipping">local_shipping</span>
            Historial de Abastecimientos
        </h4>
        <a href="{{ route('gas-deliveries.index') }}" class="text-primary text-body-sm font-semibold flex items-center gap-xs hover:underline">
            <span class="material-symbols-outlined text-[18px]">list</span>
            Ver todos
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-surface-container-low/80 border-b-2 border-primary">
                    <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant">Fecha</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant text-right">Galones</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant text-right">Monto</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant text-center">Estado</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant">Inventario</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deliveries as $delivery)
                <tr class="border-b border-outline-variant hover:bg-surface-container-lowest/50 transition-colors">
                    <td class="px-md py-md text-body-sm text-on-surface">{{ $delivery->delivery_date?->format('d/m/Y') ?? '—' }}</td>
                    <td class="px-md py-md text-body-sm text-on-surface text-right font-mono-data font-bold">{{ number_format($delivery->gallons_delivered ?? 0, 1) }} gal</td>
                    <td class="px-md py-md text-body-sm text-on-surface text-right font-mono-data">{{ $delivery->invoice_amount ? 'RD$' . number_format($delivery->invoice_amount, 2) : '—' }}</td>
                    <td class="px-md py-md text-body-sm text-center">
                        @if($delivery->status === 'completed')
                            <span class="px-3 py-1 bg-[#E3FCEF] text-[#006644] rounded-full text-[11px] font-bold uppercase tracking-wider">Completado</span>
                        @elseif($delivery->status === 'receiving')
                            <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-[11px] font-bold uppercase tracking-wider">Recibiendo</span>
                        @else
                            <span class="px-3 py-1 bg-[#E8F0FE] text-[#185ABC] rounded-full text-[11px] font-bold uppercase tracking-wider">Pendiente</span>
                        @endif
                    </td>
                    <td class="px-md py-md text-body-sm">
                        <div class="flex items-center gap-xs">
                            @php $afterPct = $setting->capacity_gallons > 0 && $delivery->tank_reading_after ? min(100, ($delivery->tank_reading_after / $setting->capacity_gallons) * 100) : 0; @endphp
                            <div class="w-24 h-2 bg-surface-container-high rounded-full overflow-hidden">
                                <div class="bg-[#006644] h-full rounded-full" style="width: {{ $afterPct }}%;"></div>
                            </div>
                            <span class="text-[12px] text-on-surface-variant">{{ $delivery->tank_reading_after ? number_format($delivery->tank_reading_after, 0) : '—' }} gal</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- SETTINGS FORM --}}
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg mb-xl">
    <h4 class="font-headline-md text-headline-md mb-lg flex items-center gap-2">
        <span class="material-symbols-outlined" data-icon="settings">settings</span>
        Configuración
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
                @error('tank_name')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-xs" for="capacity_gallons">Capacidad Máxima (galones) <span class="text-error">*</span></label>
                <input type="number" id="capacity_gallons" name="capacity_gallons" value="{{ old('capacity_gallons', $setting->capacity_gallons) }}" step="0.01" min="1" required
                    class="w-full px-md py-md border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('capacity_gallons') border-error ring-1 ring-error @enderror" />
                @error('capacity_gallons')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-xs" for="alert_min_gallons">Alerta Mínima (galones) <span class="text-error">*</span></label>
                <input type="number" id="alert_min_gallons" name="alert_min_gallons" value="{{ old('alert_min_gallons', $setting->alert_min_gallons) }}" step="0.01" min="0" required
                    class="w-full px-md py-md border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('alert_min_gallons') border-error ring-1 ring-error @enderror" />
                @error('alert_min_gallons')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-xs" for="alert_min_percentage">Alerta Mínima (%) <span class="text-error">*</span></label>
                <input type="number" id="alert_min_percentage" name="alert_min_percentage" value="{{ old('alert_min_percentage', $setting->alert_min_percentage) }}" step="0.01" min="0" max="100" required
                    class="w-full px-md py-md border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('alert_min_percentage') border-error ring-1 ring-error @enderror" />
                @error('alert_min_percentage')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-xs" for="average_consumption_method">Método Promedio de Consumo <span class="text-error">*</span></label>
                <select id="average_consumption_method" name="average_consumption_method" required
                    class="w-full px-md py-md border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('average_consumption_method') border-error ring-1 ring-error @enderror">
                    <option value="last_3_months" {{ old('average_consumption_method', $setting->average_consumption_method) === 'last_3_months' ? 'selected' : '' }}>Últimos 3 meses</option>
                    <option value="last_6_months" {{ old('average_consumption_method', $setting->average_consumption_method) === 'last_6_months' ? 'selected' : '' }}>Últimos 6 meses</option>
                    <option value="last_12_months" {{ old('average_consumption_method', $setting->average_consumption_method) === 'last_12_months' ? 'selected' : '' }}>Últimos 12 meses</option>
                </select>
                @error('average_consumption_method')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-xs" for="status">Estado <span class="text-error">*</span></label>
                <select id="status" name="status" required
                    class="w-full px-md py-md border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('status') border-error ring-1 ring-error @enderror">
                    <option value="active" {{ old('status', $setting->status) === 'active' ? 'selected' : '' }}>Activo</option>
                    <option value="inactive" {{ old('status', $setting->status) === 'inactive' ? 'selected' : '' }}>Inactivo</option>
                </select>
                @error('status')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex items-center gap-md mt-xl">
            <button type="submit" class="px-lg py-md bg-primary text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all">
                <span class="material-symbols-outlined" data-icon="save">save</span>
                Guardar
            </button>
            <button type="button" id="resetBtn" class="px-lg py-md bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
                <span class="material-symbols-outlined" data-icon="restart_alt">restart_alt</span>
                Restaurar Valores por Defecto
            </button>
        </div>
    </form>
</div>

{{-- Reset Confirmation Form (hidden) --}}
<form id="resetForm" action="{{ route('gas-tank.reset') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="condominium_id" value="{{ $setting->condominium_id }}">
</form>

{{-- Reset Confirmation Dialog --}}
<div id="resetDialog" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-lg max-w-md w-full mx-4 shadow-2xl">
        <h3 class="font-headline-md text-headline-md text-on-surface mb-md flex items-center gap-2">
            <span class="material-symbols-outlined text-amber-500" data-icon="warning">warning</span>
            Restaurar Valores por Defecto
        </h3>
        <p class="text-body-md text-on-surface-variant mb-lg">
            Se restaurarán todos los valores a los predeterminados (Capacidad: 100 gal, Alerta: 20 gal / 20%, Método: últimos 3 meses). Esta acción no se puede deshacer.
        </p>
        <div class="flex justify-end gap-md">
            <button type="button" onclick="document.getElementById('resetDialog').classList.add('hidden')" class="px-lg py-md bg-white border border-outline-variant rounded-lg hover:bg-surface-container-low transition-colors">Cancelar</button>
            <button type="button" onclick="document.getElementById('resetForm').submit()" class="px-lg py-md bg-error text-white rounded-lg hover:brightness-110 transition-all">Restaurar</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('resetBtn').addEventListener('click', function() {
    document.getElementById('resetDialog').classList.remove('hidden');
});
</script>
@endpush
@elseif($condoId)
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-xl text-center">
    <span class="material-symbols-outlined text-on-surface-variant mb-md" data-icon="propane_tank" style="font-size:48px;"></span>
    <p class="text-on-surface-variant">No hay configuración de tanque para este condominio.</p>
    <a href="{{ route('gas-tank.edit', ['condominium_id' => $condoId]) }}" class="inline-flex items-center gap-sm mt-md text-primary font-semibold hover:underline">
        <span class="material-symbols-outlined">settings</span>
        Configurar Tanque
    </a>
</div>
@endif
@endsection