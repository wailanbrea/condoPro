@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ app()->getLocale() === 'es' ? 'Imprevistos' : 'Extra Charges' }}</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">{{ app()->getLocale() === 'es' ? 'Imprevistos' : 'Extra Charges' }}</h2>
        <p class="text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Gastos imprevistos repartidos entre apartamentos' : 'Unforeseen expenses distributed among apartments' }}</p>
    </div>
    <a href="{{ route('extra-charges.create') }}" class="px-lg py-2 bg-primary text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all">
        <span class="material-symbols-outlined" data-icon="add">add</span>
        {{ app()->getLocale() === 'es' ? 'Nuevo Imprevisto' : 'New Extra Charge' }}
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

<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-low/50">
                <tr>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">Título</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">Monto Total</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">Tipo Reparto</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">Cuotas</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.common.status') }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-container-low">
                @forelse($extraCharges as $charge)
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-lg py-md font-bold text-on-surface">{{ $charge->title }}</td>
                        <td class="px-lg py-md font-mono-data text-mono-data">{{ number_format($charge->total_amount, 2) }}</td>
                        <td class="px-lg py-md">
                            @switch($charge->distribution_type)
                                @case('equal')
                                    <span class="px-2 py-1 bg-primary-fixed text-primary rounded text-[11px] font-bold uppercase tracking-wider">Igualitario</span>
                                    @break
                                @case('selected')
                                    <span class="px-2 py-1 bg-tertiary-fixed text-tertiary rounded text-[11px] font-bold uppercase tracking-wider">Seleccionados</span>
                                    @break
                                @case('percentage')
                                    <span class="px-2 py-1 bg-secondary-container text-on-secondary rounded text-[11px] font-bold uppercase tracking-wider">Porcentaje</span>
                                    @break
                                @case('manual')
                                    <span class="px-2 py-1 bg-surface-container-low text-on-surface-variant rounded text-[11px] font-bold uppercase tracking-wider">Manual</span>
                                    @break
                                @case('installments')
                                    <span class="px-2 py-1 bg-tertiary-fixed text-tertiary rounded text-[11px] font-bold uppercase tracking-wider">Cuotas</span>
                                    @break
                                @default
                                    <span class="px-2 py-1 bg-surface-container-low text-on-surface-variant rounded text-[11px] font-bold uppercase tracking-wider">{{ $charge->distribution_type }}</span>
                            @endswitch
                        </td>
                        <td class="px-lg py-md">{{ $charge->installments_count ?? 1 }}</td>
                        <td class="px-lg py-md">
                            @switch($charge->status)
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
                                    <span class="px-3 py-1 bg-surface-container-low text-on-surface-variant rounded-full text-[11px] font-bold uppercase tracking-wider">{{ $charge->status }}</span>
                            @endswitch
                        </td>
                        <td class="px-lg py-md">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('extra-charges.edit', $charge) }}" class="p-1 hover:bg-surface-container-high rounded-lg text-on-surface-variant hover:text-primary transition-colors" title="{{ __('messages.common.edit') }}">
                                    <span class="material-symbols-outlined text-lg" data-icon="edit">edit</span>
                                </a>
                                <form action="{{ route('extra-charges.destroy', $charge) }}" method="POST" onsubmit="return confirm('{{ __('messages.common.delete_confirm') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 hover:bg-surface-container-high rounded-lg text-on-surface-variant hover:text-error transition-colors" title="{{ __('messages.common.delete') }}">
                                        <span class="material-symbols-outlined text-lg" data-icon="delete">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-lg py-xl text-center text-on-surface-variant">{{ __('messages.common.no_results') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection