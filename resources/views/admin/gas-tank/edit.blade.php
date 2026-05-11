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

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">Configuración del Tanque Principal</h2>
        <p class="text-on-surface-variant">Capacidad, alertas y método de cálculo del inventario de gas</p>
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

@if($condoId && $setting)

{{-- Tank Visualization --}}
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg mb-xl">
    <h4 class="font-headline-md text-headline-md mb-lg flex items-center gap-2">
        <span class="material-symbols-outlined" data-icon="water_drop">water_drop</span>
        {{ $setting->tank_name }}
    </h4>

    @if($tankData)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-xl">
        {{-- Tank Gauge --}}
        <div class="flex flex-col items-center justify-center">
            <div class="relative" style="width: 200px; height: 280px;">
                {{-- Tank Body --}}
                <div class="absolute inset-0 rounded-2xl border-4 overflow-hidden" style="border-color: #D1D5DB; background: #F3F4F6;">
                    {{-- Gas Level --}}
                    @php
                        $fillHeight = $tankData['percentage'];
                        $fillColor = $tankData['status'] === 'low' ? '#EF4444' : '#3B82F6';
                        $waveColor = $tankData['status'] === 'low' ? '#DC2626' : '#2563EB';
                    @endphp
                    <div class="absolute bottom-0 left-0 right-0 transition-all duration-1000 ease-in-out"
                         style="height: {{ $fillHeight }}%; background: linear-gradient(180deg, {{ $fillColor }}22 0%, {{ $fillColor }}88 30%, {{ $waveColor }} 100%);">
                        {{-- Wave Effect --}}
                        <div class="absolute -top-2 left-0 right-0 overflow-hidden" style="height: 20px;">
                            <svg viewBox="0 0 200 20" class="w-full" style="margin-top: -2px;">
                                <path d="M0,10 C25,18 50,2 100,10 C150,18 175,2 200,10 L200,20 L0,20 Z" fill="{{ $fillColor }}66" />
                                <path d="M0,12 C30,4 60,16 100,8 C140,0 170,14 200,8 L200,20 L0,20 Z" fill="{{ $fillColor }}44" />
                            </svg>
                        </div>
                    </div>

                    {{-- Percentage Overlay --}}
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <p class="font-bold text-on-surface" style="font-size: 2.5rem; line-height: 1; text-shadow: 0 1px 2px white; {{ $fillHeight < 25 ? 'color: #1F2937;' : '' }}">{{ number_format($tankData['percentage'], 0) }}%</p>
                            <p class="text-sm font-medium" style="text-shadow: 0 1px 2px white;">{{ number_format($tankData['estimatedInventory'], 1) }} gal</p>
                        </div>
                    </div>
                </div>

                {{-- Tank Top Cap --}}
                <div class="absolute -top-2 left-4 right-4 h-4 rounded-t-lg" style="background: #9CA3AF;"></div>
                {{-- Pipe Left --}}
                <div class="absolute -top-6 left-8 w-3 h-6 rounded-t-sm" style="background: #9CA3AF;"></div>
                {{-- Pipe Right --}}
                <div class="absolute -top-6 right-8 w-3 h-6 rounded-t-sm" style="background: #9CA3AF;"></div>
            </div>

            {{-- Status Badge --}}
            <div class="mt-md text-center">
                @if($tankData['status'] === 'low')
                    <div class="flex items-center gap-2 px-md py-sm bg-red-50 border border-red-200 rounded-lg">
                        <span class="material-symbols-outlined text-red-600" data-icon="warning">warning</span>
                        <span class="font-bold text-red-700">Nivel de gas bajo. Se recomienda solicitar abastecimiento.</span>
                    </div>
                @else
                    <div class="flex items-center gap-2 px-md py-sm bg-green-50 border border-green-200 rounded-lg">
                        <span class="material-symbols-outlined text-green-600" data-icon="check_circle">check_circle</span>
                        <span class="font-bold text-green-700">Nivel Normal</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 gap-md">
            <div class="bg-surface-container-lowest rounded-lg p-md text-center border-l-4 border-primary">
                <p class="text-label-caps text-on-surface-variant font-label-caps mb-xs">Capacidad</p>
                <p class="font-display-xl text-display-xl text-on-surface">{{ number_format($tankData['capacity'], 0) }}</p>
                <p class="text-body-sm text-on-surface-variant">galones</p>
            </div>
            <div class="bg-surface-container-lowest rounded-lg p-md text-center border-l-4 border-blue-500">
                <p class="text-label-caps text-on-surface-variant font-label-caps mb-xs">Inventario Estimado</p>
                <p class="font-display-xl text-display-xl text-on-surface">{{ number_format($tankData['estimatedInventory'], 1) }}</p>
                <p class="text-body-sm text-on-surface-variant">galones</p>
            </div>
            <div class="bg-surface-container-lowest rounded-lg p-md text-center border-l-4 border-amber-500">
                <p class="text-label-caps text-on-surface-variant font-label-caps mb-xs">Consumido</p>
                <p class="font-display-xl text-display-xl text-on-surface">{{ number_format($tankData['totalConsumption'], 1) }}</p>
                <p class="text-body-sm text-on-surface-variant">galones</p>
            </div>
            <div class="bg-surface-container-lowest rounded-lg p-md text-center border-l-4 border-green-500">
                <p class="text-label-caps text-on-surface-variant font-label-caps mb-xs">Recibido</p>
                <p class="font-display-xl text-display-xl text-on-surface">{{ number_format($tankData['totalDelivered'], 1) }}</p>
                <p class="text-body-sm text-on-surface-variant">galones</p>
            </div>
            <div class="bg-surface-container-lowest rounded-lg p-md text-center border-l-4 {{ $tankData['status'] === 'low' ? 'border-red-500' : 'border-tertiary' }}">
                <p class="text-label-caps text-on-surface-variant font-label-caps mb-xs">Alerta Mínima</p>
                <p class="font-display-xl text-display-xl text-on-surface">{{ number_format($setting->alert_min_gallons, 0) }}</p>
                <p class="text-body-sm text-on-surface-variant">gal ({{ $setting->alert_min_percentage }}%)</p>
            </div>
            @if($tankData['lastDelivery'])
            <div class="bg-surface-container-lowest rounded-lg p-md text-center border-l-4 border-purple-500">
                <p class="text-label-caps text-on-surface-variant font-label-caps mb-xs">Última Recepción</p>
                <p class="font-headline-md text-headline-md text-on-surface">{{ $tankData['lastDelivery']->delivery_date?->format('d/m/Y') ?? '—' }}</p>
                <p class="text-body-sm text-on-surface-variant">{{ number_format($tankData['lastDelivery']->gallons_delivered ?? 0, 1) }} gal</p>
            </div>
            @endif
        </div>
    </div>
    @else
    <div class="text-center py-lg">
        <span class="material-symbols-outlined text-on-surface-variant mb-md" data-icon="propane_tank" style="font-size:48px;"></span>
        <p class="text-on-surface-variant">El tanque está configurado pero no hay datos de inventario aún.</p>
        <p class="text-body-sm text-on-surface-variant mt-sm">Registre recepciones de gas para ver el nivel del tanque.</p>
    </div>
    @endif
</div>

{{-- Settings Form --}}
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
@else
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-xl text-center">
    <span class="material-symbols-outlined text-on-surface-variant mb-md" data-icon="propane_tank" style="font-size:48px;"></span>
    <p class="text-on-surface-variant">Seleccione un condominio para configurar el tanque.</p>
</div>
@endif
@endsection