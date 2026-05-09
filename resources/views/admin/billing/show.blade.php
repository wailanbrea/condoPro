@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <a href="{{ route('billing.index') }}" class="text-body-sm hover:text-primary transition-colors">{{ __('messages.billing.title') }}</a>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ __('messages.billing.detail_title') }}</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">{{ __('messages.billing.detail_title') }}</h2>
        <p class="text-on-surface-variant">{{ $billing->apartment->number ?? '—' }} — {{ \Carbon\Carbon::create()->month($billing->billing_month)->translatedFormat('F') }} {{ $billing->billing_year }}</p>
    </div>
    <div class="flex gap-md">
        <a href="{{ route('billing.edit', $billing) }}" class="px-lg py-2 bg-primary text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all">
            <span class="material-symbols-outlined" data-icon="edit">edit</span>
            {{ __('messages.common.edit') }}
        </a>
        <a href="{{ route('billing.index') }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter mb-xl">
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-primary">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.payments.apartment') }}</p>
        <h3 class="font-headline-md text-headline-md text-on-surface">{{ $billing->apartment->number ?? '—' }}</h3>
        <p class="text-body-sm text-on-surface-variant">{{ $billing->apartment->owner_name ?? '—' }}</p>
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-secondary">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.common.total') }}</p>
        <h3 class="font-headline-lg text-headline-lg text-secondary">RD${{ number_format($billing->total, 2) }}</h3>
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-outline">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.common.status') }}</p>
        @switch($billing->status)
            @case('pending')
                <span class="px-3 py-1 bg-tertiary-fixed text-tertiary rounded-full text-[11px] font-bold uppercase tracking-wider">{{ __('messages.common.pending') }}</span>
                @break
            @case('paid')
                <span class="px-3 py-1 bg-[#E3FCEF] text-[#006644] rounded-full text-[11px] font-bold uppercase tracking-wider">{{ __('messages.common.paid') }}</span>
                @break
            @case('overdue')
                <span class="px-3 py-1 bg-[#FFEBE6] text-[#BF2600] rounded-full text-[11px] font-bold uppercase tracking-wider">{{ __('messages.common.overdue') }}</span>
                @break
            @case('cancelled')
                <span class="px-3 py-1 bg-surface-container-low text-on-surface-variant rounded-full text-[11px] font-bold uppercase tracking-wider">{{ __('messages.common.cancelled') }}</span>
                @break
            @default
                <span class="px-3 py-1 bg-surface-container-low text-on-surface-variant rounded-full text-[11px] font-bold uppercase tracking-wider">{{ $billing->status }}</span>
        @endswitch
        <p class="text-body-sm text-on-surface-variant mt-sm">{{ __('messages.billing.due_date') }}: {{ $billing->due_date ? $billing->due_date->format('d/m/Y') : '—' }}</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] overflow-hidden mb-xl">
    <div class="p-lg border-b border-surface-container-low">
        <h4 class="font-headline-md text-headline-md">{{ __('messages.billing.items') }}</h4>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-low/50">
                <tr>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.billing.concept_type') }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.billing.description') }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant text-right">{{ __('messages.billing.amount') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-container-low">
                @forelse($billing->billItems as $item)
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-lg py-md">
                            @switch($item->concept_type)
                                @case('maintenance')
                                    <span class="px-2 py-1 bg-primary-fixed text-primary rounded text-[11px] font-bold uppercase tracking-wider">{{ __('messages.billing.concept_maintenance') }}</span>
                                    @break
                                @case('gas')
                                    <span class="px-2 py-1 bg-tertiary-fixed text-tertiary rounded text-[11px] font-bold uppercase tracking-wider">{{ __('messages.billing.concept_gas') }}</span>
                                    @break
                                @case('extra_charge')
                                    <span class="px-2 py-1 bg-secondary-container text-on-secondary rounded text-[11px] font-bold uppercase tracking-wider">{{ __('messages.billing.concept_extra_charge') }}</span>
                                    @break
                                @default
                                    <span class="px-2 py-1 bg-surface-container-low text-on-surface-variant rounded text-[11px] font-bold uppercase tracking-wider">{{ $item->concept_type }}</span>
                            @endswitch
                        </td>
                        <td class="px-lg py-md text-on-surface-variant">{{ $item->description }}</td>
                        <td class="px-lg py-md font-mono-data text-mono-data text-right">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-lg py-xl text-center text-on-surface-variant">{{ __('messages.common.no_results') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg">
    <div class="flex justify-between items-center border-b border-surface-container-low pb-md mb-md">
        <span class="text-on-surface-variant font-label-caps text-label-caps">{{ __('messages.billing.subtotal') }}</span>
        <span class="font-mono-data text-mono-data">{{ number_format($billing->subtotal, 2) }}</span>
    </div>
    <div class="flex justify-between items-center border-b border-surface-container-low pb-md mb-md">
        <span class="text-on-surface-variant font-label-caps text-label-caps">{{ __('messages.billing.previous_balance') }}</span>
        <span class="font-mono-data text-mono-data">{{ number_format($billing->previous_balance, 2) }}</span>
    </div>
    @if($billing->payments_applied > 0)
        <div class="flex justify-between items-center border-b border-surface-container-low pb-md mb-md">
            <span class="text-on-surface-variant font-label-caps text-label-caps">{{ __('messages.billing.payments_applied') }}</span>
            <span class="font-mono-data text-mono-data text-secondary">-{{ number_format($billing->payments_applied, 2) }}</span>
        </div>
    @endif
    <div class="flex justify-between items-center pt-md">
        <span class="font-headline-md text-headline-md text-on-surface">{{ __('messages.common.total') }}</span>
        <span class="font-headline-lg text-headline-lg text-primary">RD${{ number_format($billing->total, 2) }}</span>
    </div>
</div>
@endsection