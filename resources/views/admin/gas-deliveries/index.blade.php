@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <a href="{{ route('gas.index') }}" class="text-body-sm hover:text-primary transition-colors">Gas</a>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">Historial de Recepciones</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">Historial de Recepciones de Gas</h2>
        <p class="text-on-surface-variant">Registro de las veces que se ha comprado gas para el condominio</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-lg px-lg py-md bg-[#E3FCEF] text-[#006644] rounded-lg flex items-center gap-2 border border-[#006644]/20">
        <span class="material-symbols-outlined" data-icon="check_circle">check_circle</span>
        {{ session('success') }}
    </div>
@endif

{{-- Condominium Filter --}}
@if(auth()->user()->role === 'super_admin')
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-md mb-xl">
    <form method="GET" action="{{ route('gas-deliveries.index') }}" class="flex items-end gap-md">
        <div class="flex-1">
            <label class="block font-label-caps text-label-caps text-on-surface-variant mb-xs" for="condominium_id">Condominio</label>
            <select name="condominium_id" id="condominium_id"
                class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface">
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

{{-- Deliveries Table --}}
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] overflow-hidden">
    @if($deliveries->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-surface-container-low/80 border-b-2 border-primary">
                    <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant">Fecha</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-on-surface-variant">Condominio</th>
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
                    <td class="px-md py-md text-body-sm text-on-surface">{{ $delivery->condominium?->name ?? '—' }}</td>
                    <td class="px-md py-md text-body-sm text-on-surface text-right font-mono-data">{{ $delivery->tank_reading_before ? number_format($delivery->tank_reading_before, 3) : '—' }}</td>
                    <td class="px-md py-md text-body-sm text-on-surface text-right font-mono-data">{{ $delivery->tank_reading_after ? number_format($delivery->tank_reading_after, 3) : '—' }}</td>
                    <td class="px-md py-md text-body-sm text-on-surface text-right font-mono-data">{{ $delivery->gallons_delivered ? number_format($delivery->gallons_delivered, 2) : '—' }} gal</td>
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
    @else
    <div class="p-xl text-center">
        <span class="material-symbols-outlined text-on-surface-variant mb-md" data-icon="local_shipping" style="font-size:48px;"></span>
        <p class="text-on-surface-variant">No hay recepciones de gas registradas.</p>
    </div>
    @endif
</div>
@endsection