@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ __('messages.billing.title') }}</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">{{ __('messages.billing.title') }}</h2>
        <p class="text-on-surface-variant">{{ __('messages.billing.list') }}</p>
    </div>
    <a href="{{ route('billing.create') }}" class="px-lg py-2 bg-primary text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all">
        <span class="material-symbols-outlined" data-icon="add">add</span>
        {{ __('messages.common.create') }}
    </a>
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

{{-- Filters --}}
<form method="GET" action="{{ route('billing.index') }}" class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg mb-lg">
    <div class="grid grid-cols-2 md:grid-cols-5 gap-md items-end">
        @if(auth()->user()->role === 'super_admin')
        <div>
            <label class="block text-label-caps font-label-caps text-on-surface-variant mb-xs">{{ __('messages.common.condominium') ?? 'Condominio' }}</label>
            <select name="condominium_id" class="w-full border border-outline-variant rounded-lg px-md py-sm text-on-surface bg-surface-container-lowest">
                @foreach($condominiums as $condo)
                    <option value="{{ $condo->id }}" {{ $condoId == $condo->id ? 'selected' : '' }}>{{ $condo->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div>
            <label class="block text-label-caps font-label-caps text-on-surface-variant mb-xs">Mes</label>
            <select name="month" class="w-full border border-outline-variant rounded-lg px-md py-sm text-on-surface bg-surface-container-lowest">
                @foreach($months as $m => $name)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-label-caps font-label-caps text-on-surface-variant mb-xs">Año</label>
            <select name="year" class="w-full border border-outline-variant rounded-lg px-md py-sm text-on-surface bg-surface-container-lowest">
                @for($y = now()->year + 1; $y >= 2020; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="block text-label-caps font-label-caps text-on-surface-variant mb-xs">Tipo</label>
            <select name="type" class="w-full border border-outline-variant rounded-lg px-md py-sm text-on-surface bg-surface-container-lowest">
                <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Todas</option>
                <option value="maintenance" {{ $type === 'maintenance' ? 'selected' : '' }}>Mantenimiento</option>
                <option value="gas" {{ $type === 'gas' ? 'selected' : '' }}>Gas</option>
                <option value="extra_charge" {{ $type === 'extra_charge' ? 'selected' : '' }}>Cuota Extra</option>
            </select>
        </div>
        <div class="flex gap-sm">
            <button type="submit" class="px-lg py-sm bg-primary text-white rounded-lg flex items-center gap-sm hover:brightness-110 transition-all">
                <span class="material-symbols-outlined text-lg" data-icon="search">search</span>
                Buscar
            </button>
            <a href="{{ route('billing.index') }}" class="px-lg py-sm border border-outline-variant rounded-lg text-on-surface-variant hover:bg-surface-container-low transition-all">Limpiar</a>
        </div>
    </div>
</form>

{{-- Summary Cards --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-md mb-lg">
    <div class="bg-white rounded-xl p-md shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-primary">
        <p class="text-label-caps text-on-surface-variant font-label-caps">{{ __('messages.common.condominium') ?? 'Usuarios' }}</p>
        <p class="font-headline-lg text-headline-lg text-on-surface">{{ $summary->users_count }}</p>
    </div>
    <div class="bg-white rounded-xl p-md shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-secondary">
        <p class="text-label-caps text-on-surface-variant font-label-caps">Facturas</p>
        <p class="font-headline-lg text-headline-lg text-on-surface">{{ $summary->invoices_count }}</p>
    </div>
    <div class="bg-white rounded-xl p-md shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-amber-500">
        <p class="text-label-caps text-on-surface-variant font-label-caps">Pendientes</p>
        <p class="font-headline-lg text-headline-lg text-amber-600">{{ $summary->pending_invoices_count }}</p>
    </div>
    <div class="bg-white rounded-xl p-md shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-tertiary">
        <p class="text-label-caps text-on-surface-variant font-label-caps">Total Facturado</p>
        <p class="font-headline-lg text-headline-lg text-on-surface">RD${{ number_format($summary->total_billed, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl p-md shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-error">
        <p class="text-label-caps text-on-surface-variant font-label-caps">Total Pendiente</p>
        <p class="font-headline-lg text-headline-lg text-error">RD${{ number_format($summary->total_pending, 2) }}</p>
    </div>
</div>

{{-- Grouped User Cards --}}
@if($grouped->isEmpty())
    <div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-xl text-center">
        <span class="material-symbols-outlined text-5xl text-on-surface-variant/40" data-icon="receipt_long">receipt_long</span>
        <p class="text-on-surface-variant mt-md">No hay facturas para el período seleccionado.</p>
    </div>
@else
    @foreach($grouped as $groupKey => $group)
    <div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] mb-md overflow-hidden user-group-card">
        <button type="button" class="w-full px-lg py-md flex items-center justify-between hover:bg-surface-container-low transition-colors group-toggle" data-target="group-{{ $loop->iteration }}">
            <div class="flex items-center gap-md flex-1 min-w-0">
                <div class="w-10 h-10 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center flex-shrink-0">
                    <span class="font-bold text-sm">{{ strtoupper(substr($group->user_name, 0, 1)) }}</span>
                </div>
                <div class="min-w-0 text-left">
                    <h3 class="font-title-md text-on-surface truncate">{{ $group->user_name }}</h3>
                    <p class="text-body-sm text-on-surface-variant">Apto: {{ $group->apartment_numbers }}</p>
                </div>
            </div>
            <div class="flex items-center gap-lg flex-shrink-0">
                <div class="text-right hidden md:block">
                    <p class="text-body-sm text-on-surface-variant">{{ $group->invoices_count }} factura{{ $group->invoices_count !== 1 ? 's' : '' }}</p>
                    <p class="text-body-sm text-on-surface-variant font-bold">RD${{ number_format($group->total_pending, 2) }}</p>
                </div>
                @if($group->pending_invoices_count > 0)
                    <span class="px-2 py-xs bg-amber-100 text-amber-800 rounded-full text-[10px] font-bold uppercase tracking-wider">{{ $group->pending_invoices_count }} pendiente{{ $group->pending_invoices_count !== 1 ? 's' : '' }}</span>
                @else
                    <span class="px-2 py-xs bg-[#E3FCEF] text-[#006644] rounded-full text-[10px] font-bold uppercase tracking-wider">Al día</span>
                @endif
                <span class="material-symbols-outlined text-on-surface-variant transition-transform" data-icon="expand_more">expand_more</span>
            </div>
        </button>

        <div id="group-{{ $loop->iteration }}" class="hidden border-t border-outline-variant">
            {{-- User summary row --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-sm px-lg py-md bg-surface-container-low/50">
                <div>
                    <p class="text-label-caps text-on-surface-variant font-label-caps">Total Facturado</p>
                    <p class="font-mono-data text-on-surface font-bold">RD${{ number_format($group->total_billed, 2) }}</p>
                </div>
                <div>
                    <p class="text-label-caps text-on-surface-variant font-label-caps">Total Pagado</p>
                    <p class="font-mono-data text-on-surface font-bold">RD${{ number_format($group->total_paid, 2) }}</p>
                </div>
                <div>
                    <p class="text-label-caps text-on-surface-variant font-label-caps">Total Pendiente</p>
                    <p class="font-mono-data text-error font-bold">RD${{ number_format($group->total_pending, 2) }}</p>
                </div>
                <div>
                    <p class="text-label-caps text-on-surface-variant font-label-caps">Estado</p>
                    <p class="text-on-surface font-bold">{{ $group->pending_invoices_count }} pendiente{{ $group->pending_invoices_count !== 1 ? 's' : '' }}, {{ $group->paid_invoices_count }} pagada{{ $group->paid_invoices_count !== 1 ? 's' : '' }}</p>
                </div>
            </div>

            {{-- Bills Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-surface-container-low/80">
                        <tr>
                            <th class="px-md py-sm text-label-caps font-label-caps text-on-surface-variant">Factura</th>
                            <th class="px-md py-sm text-label-caps font-label-caps text-on-surface-variant">Tipo</th>
                            <th class="px-md py-sm text-label-caps font-label-caps text-on-surface-variant">Período</th>
                            <th class="px-md py-sm text-label-caps font-label-caps text-on-surface-variant text-right">Total</th>
                            <th class="px-md py-sm text-label-caps font-label-caps text-on-surface-variant text-right">Pagado</th>
                            <th class="px-md py-sm text-label-caps font-label-caps text-on-surface-variant text-right">Pendiente</th>
                            <th class="px-md py-sm text-label-caps font-label-caps text-on-surface-variant">Estado</th>
                            <th class="px-md py-sm text-label-caps font-label-caps text-on-surface-variant">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @foreach($group->bills as $bill)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-md py-sm">
                                <a href="{{ route('billing.show', $bill) }}" class="text-primary hover:underline font-bold">#{{ $bill->id }}</a>
                                <p class="text-body-xs text-on-surface-variant">{{ $bill->apartment?->number ?? '—' }}</p>
                            </td>
                            <td class="px-md py-sm">
                                <div class="flex flex-wrap gap-xs">
                                    @foreach($bill->billItems as $item)
                                        @switch($item->concept_type)
                                            @case('maintenance')
                                                <span class="px-2 py-xs bg-blue-100 text-blue-800 rounded text-[10px] font-bold uppercase">Mantenimiento</span>
                                                @break
                                            @case('gas')
                                                <span class="px-2 py-xs bg-orange-100 text-orange-800 rounded text-[10px] font-bold uppercase">Gas</span>
                                                @break
                                            @case('extra_charge')
                                                <span class="px-2 py-xs bg-purple-100 text-purple-800 rounded text-[10px] font-bold uppercase">Cuota Extra</span>
                                                @break
                                            @default
                                                <span class="px-2 py-xs bg-surface-container-low text-on-surface-variant rounded text-[10px]">{{ $item->concept_type }}</span>
                                        @endswitch
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-md py-sm text-on-surface-variant">{{ $months[$bill->billing_month] ?? $bill->billing_month }} {{ $bill->billing_year }}</td>
                            <td class="px-md py-sm font-mono-data text-on-surface text-right">RD${{ number_format($bill->total, 2) }}</td>
                            <td class="px-md py-sm font-mono-data text-on-surface-variant text-right">RD${{ number_format($bill->payments_applied, 2) }}</td>
                            <td class="px-md py-sm font-mono-data font-bold text-right {{ max(0, $bill->total - $bill->payments_applied) > 0 ? 'text-error' : 'text-[#006644]' }}">RD${{ number_format(max(0, $bill->total - $bill->payments_applied), 2) }}</td>
                            <td class="px-md py-sm">
                                @switch($bill->status)
                                    @case('pending')
                                        <span class="px-2 py-xs bg-amber-100 text-amber-800 rounded-full text-[10px] font-bold uppercase tracking-wider">Pendiente</span>
                                        @break
                                    @case('paid')
                                        <span class="px-2 py-xs bg-[#E3FCEF] text-[#006644] rounded-full text-[10px] font-bold uppercase tracking-wider">Pagada</span>
                                        @break
                                    @case('partial')
                                        <span class="px-2 py-xs bg-blue-100 text-blue-800 rounded-full text-[10px] font-bold uppercase tracking-wider">Parcial</span>
                                        @break
                                    @case('overdue')
                                        <span class="px-2 py-xs bg-[#FFEBE6] text-[#BF2600] rounded-full text-[10px] font-bold uppercase tracking-wider">Vencida</span>
                                        @break
                                    @case('cancelled')
                                        <span class="px-2 py-xs bg-surface-container-low text-on-surface-variant rounded-full text-[10px] font-bold uppercase tracking-wider">Anulada</span>
                                        @break
                                    @default
                                        <span class="px-2 py-xs bg-surface-container-low text-on-surface-variant rounded-full text-[10px]">{{ $bill->status }}</span>
                                @endswitch
                            </td>
                            <td class="px-md py-sm">
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('billing.show', $bill) }}" class="p-1 hover:bg-surface-container-high rounded-lg text-on-surface-variant hover:text-primary transition-colors" title="Ver">
                                        <span class="material-symbols-outlined text-lg" data-icon="visibility">visibility</span>
                                    </a>
                                    @if($bill->status !== 'paid')
                                    <a href="{{ route('billing.edit', $bill) }}" class="p-1 hover:bg-surface-container-high rounded-lg text-on-surface-variant hover:text-primary transition-colors" title="Editar">
                                        <span class="material-symbols-outlined text-lg" data-icon="edit">edit</span>
                                    </a>
                                    @endif
                                    <a href="{{ route('billing.pdf', $bill) }}" class="p-1 hover:bg-surface-container-high rounded-lg text-on-surface-variant hover:text-primary transition-colors" title="PDF">
                                        <span class="material-symbols-outlined text-lg" data-icon="picture_as_pdf">picture_as_pdf</span>
                                    </a>
                                    <form action="{{ route('billing.destroy', $bill) }}" method="POST" onsubmit="return confirm('¿Eliminar esta factura?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1 hover:bg-surface-container-high rounded-lg text-on-surface-variant hover:text-error transition-colors" title="Eliminar">
                                            <span class="material-symbols-outlined text-lg" data-icon="delete">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.group-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const target = document.getElementById(this.dataset.target);
            const icon = this.querySelector('[data-icon="expand_more"]');
            if (target) {
                target.classList.toggle('hidden');
                if (icon) {
                    icon.style.transform = target.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
                }
            }
        });
    });
});
</script>
@endpush
@endsection