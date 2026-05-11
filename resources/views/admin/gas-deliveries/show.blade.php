@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <a href="{{ route('gas.index') }}" class="text-body-sm hover:text-primary transition-colors">Gas</a>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <a href="{{ route('gas-tank.edit', ['condominium_id' => $gasDelivery->condominium_id]) }}" class="text-body-sm hover:text-primary transition-colors">Tanque Principal</a>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">Detalle</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">Detalle de Recepción de Gas</h2>
        <p class="text-on-surface-variant">{{ $gasDelivery->condominium?->name ?? '—' }}</p>
    </div>
    <a href="{{ route('gas-tank.edit', ['condominium_id' => $gasDelivery->condominium_id]) }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
        <span class="material-symbols-outlined" data-icon="arrow_back">arrow_back</span>
        Volver al Tanque Principal
    </a>
</div>

{{-- Status Badges --}}
<div class="grid grid-cols-1 lg:grid-cols-4 gap-gutter mb-xl">
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-primary">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">Fecha</p>
        <h3 class="font-headline-md text-headline-md text-on-surface">{{ $gasDelivery->delivery_date?->format('d/m/Y') ?? '—' }}</h3>
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-secondary">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">Condominio</p>
        <h3 class="font-headline-md text-headline-md text-on-surface">{{ $gasDelivery->condominium?->name ?? '—' }}</h3>
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-tertiary">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">Galones Recibidos</p>
        <h3 class="font-headline-md text-headline-md text-on-surface">{{ $gasDelivery->gallons_delivered ? number_format($gasDelivery->gallons_delivered, 2) . ' gal' : '—' }}</h3>
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 {{ $gasDelivery->status === 'completed' ? 'border-[#006644]' : ($gasDelivery->status === 'receiving' ? 'border-amber-500' : 'border-blue-500') }}">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">Estado</p>
        @if($gasDelivery->status === 'completed')
            <span class="px-3 py-1 bg-[#E3FCEF] text-[#006644] rounded-full text-[11px] font-bold uppercase tracking-wider">Completado</span>
        @elseif($gasDelivery->status === 'receiving')
            <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-[11px] font-bold uppercase tracking-wider">Recibiendo</span>
        @else
            <span class="px-3 py-1 bg-[#E8F0FE] text-[#185ABC] rounded-full text-[11px] font-bold uppercase tracking-wider">Pendiente</span>
        @endif
    </div>
</div>

{{-- Readings Table --}}
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg mb-xl">
    <h4 class="font-headline-md text-headline-md mb-lg flex items-center gap-2">
        <span class="material-symbols-outlined" data-icon="local_gas_station">local_gas_station</span>
        Lecturas del Tanque
    </h4>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-surface-container-low/80 border-b-2 border-primary">
                    <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant">Campo</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant text-right">Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b border-outline-variant">
                    <td class="px-md py-md text-body-sm text-on-surface">Lectura del Tanque (Antes)</td>
                    <td class="px-md py-md text-body-sm text-on-surface text-right font-mono-data">{{ $gasDelivery->tank_reading_before ? number_format($gasDelivery->tank_reading_before, 3) . ' gal' : '—' }}</td>
                </tr>
                <tr class="border-b border-outline-variant bg-surface-container-lowest">
                    <td class="px-md py-md text-body-sm text-on-surface font-bold">Lectura del Tanque (Después)</td>
                    <td class="px-md py-md text-body-sm text-on-surface text-right font-mono-data font-bold">{{ $gasDelivery->tank_reading_after ? number_format($gasDelivery->tank_reading_after, 3) . ' gal' : '—' }}</td>
                </tr>
                <tr class="border-b border-outline-variant">
                    <td class="px-md py-md text-body-sm text-on-surface">Lectura del Camión</td>
                    <td class="px-md py-md text-body-sm text-on-surface text-right font-mono-data">{{ $gasDelivery->truck_meter_reading ? number_format($gasDelivery->truck_meter_reading, 3) . ' gal' : '—' }}</td>
                </tr>
                <tr class="border-b border-outline-variant bg-surface-container-lowest">
                    <td class="px-md py-md text-body-sm text-on-surface font-bold">Galones Recibidos</td>
                    <td class="px-md py-md text-body-sm text-on-surface text-right font-mono-data font-bold">{{ $gasDelivery->gallons_delivered ? number_format($gasDelivery->gallons_delivered, 2) . ' gal' : '—' }}</td>
                </tr>
                <tr class="border-b border-outline-variant">
                    <td class="px-md py-md text-body-sm text-on-surface">Monto de la Factura</td>
                    <td class="px-md py-md text-body-sm text-on-surface text-right font-mono-data {{ $gasDelivery->invoice_amount ? 'font-bold' : '' }}">{{ $gasDelivery->invoice_amount ? 'RD$' . number_format($gasDelivery->invoice_amount, 2) : '—' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Photos --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-gutter mb-xl">
    @if($gasDelivery->tank_photo_before_path)
    <div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg">
        <h4 class="font-label-caps text-label-caps text-on-surface-variant mb-sm">Foto Tanque (Antes)</h4>
        <img src="{{ Storage::url($gasDelivery->tank_photo_before_path) }}" alt="Tanque antes" class="w-full rounded-lg object-contain max-h-64">
    </div>
    @endif

    @if($gasDelivery->tank_photo_after_path)
    <div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg">
        <h4 class="font-label-caps text-label-caps text-on-surface-variant mb-sm">Foto Tanque (Después)</h4>
        <img src="{{ Storage::url($gasDelivery->tank_photo_after_path) }}" alt="Tanque después" class="w-full rounded-lg object-contain max-h-64">
    </div>
    @endif

    @if($gasDelivery->truck_photo_path)
    <div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg">
        <h4 class="font-label-caps text-label-caps text-on-surface-variant mb-sm">Foto Contador Camión</h4>
        <img src="{{ Storage::url($gasDelivery->truck_photo_path) }}" alt="Contador camión" class="w-full rounded-lg object-contain max-h-64">
    </div>
    @endif

    @if($gasDelivery->invoice_photo_path)
    <div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg">
        <h4 class="font-label-caps text-label-caps text-on-surface-variant mb-sm">Foto Factura</h4>
        <img src="{{ Storage::url($gasDelivery->invoice_photo_path) }}" alt="Factura" class="w-full rounded-lg object-contain max-h-64">
    </div>
    @endif
</div>

{{-- Notes --}}
@if($gasDelivery->notes)
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg mb-xl">
    <h4 class="font-headline-md text-headline-md mb-sm flex items-center gap-2">
        <span class="material-symbols-outlined" data-icon="notes">notes</span>
        Notas
    </h4>
    <p class="text-body-md text-on-surface-variant">{{ $gasDelivery->notes }}</p>
</div>
@endif

{{-- Invoice Distribution --}}
@if($gasDelivery->status === 'completed' && $gasDelivery->invoice_amount && $gasDelivery->gallons_delivered && $totalConsumption > 0)
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg mb-xl">
    <h4 class="font-headline-md text-headline-md mb-lg flex items-center gap-2">
        <span class="material-symbols-outlined" data-icon="account_balance_wallet">account_balance_wallet</span>
        Distribución de la Factura por Departamento
    </h4>
    <p class="text-body-sm text-on-surface-variant mb-md">
        Total factura: <strong>RD${{ number_format($gasDelivery->invoice_amount, 2) }}</strong> |
        Total consumo del período: <strong>{{ number_format($totalConsumption, 2) }} gal</strong> |
        Costo por galón: <strong>RD${{ number_format($gasDelivery->invoice_amount / $totalConsumption, 2) }}/gal</strong>
    </p>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-surface-container-low/80 border-b-2 border-primary">
                    <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant">Apartamento</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant text-right">Consumo (gal)</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant text-right">Porcentaje</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant text-right">Monto a Pagar</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $costPerGallon = $totalConsumption > 0 ? $gasDelivery->invoice_amount / $totalConsumption : 0;
                    $apartmentReadings = \App\Models\GasReading::where('condominium_id', $gasDelivery->condominium_id)
                        ->whereMonth('created_at', $gasDelivery->delivery_date?->month)
                        ->whereYear('created_at', $gasDelivery->delivery_date?->year)
                        ->with('apartment')
                        ->get()
                        ->groupBy('apartment_id');
                @endphp
                @foreach($apartmentReadings as $apartmentId => $readings)
                    @php
                        $aptTotal = $readings->sum('gallons');
                        $percentage = $totalConsumption > 0 ? ($aptTotal / $totalConsumption) * 100 : 0;
                        $aptAmount = $aptTotal * $costPerGallon;
                        $apt = $readings->first()->apartment;
                    @endphp
                    <tr class="border-b border-outline-variant">
                        <td class="px-md py-md text-body-sm text-on-surface">{{ $apt?->number ?? 'Apt ' . $apartmentId }}</td>
                        <td class="px-md py-md text-body-sm text-on-surface text-right font-mono-data">{{ number_format($aptTotal, 2) }}</td>
                        <td class="px-md py-md text-body-sm text-on-surface text-right font-mono-data">{{ number_format($percentage, 1) }}%</td>
                        <td class="px-md py-md text-body-sm text-on-surface text-right font-mono-data font-bold">RD${{ number_format($aptAmount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-primary border-t-2 border-primary">
                    <td class="px-md py-md text-white font-bold" style="color: white !important;">Total</td>
                    <td class="px-md py-md text-white text-right font-mono-data font-bold" style="color: white !important;">{{ number_format($totalConsumption, 2) }} gal</td>
                    <td class="px-md py-md text-white text-right font-mono-data font-bold" style="color: white !important;">100%</td>
                    <td class="px-md py-md text-white text-right font-mono-data font-bold" style="color: white !important;">RD${{ number_format($gasDelivery->invoice_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif

{{-- Created By --}}
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg">
    <p class="text-body-sm text-on-surface-variant">Registrado por: <strong>{{ $gasDelivery->creator?->name ?? '—' }}</strong> el {{ $gasDelivery->created_at->format('d/m/Y H:i') }}</p>
</div>
@endsection