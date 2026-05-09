@extends('layouts.resident')

@section('content')
<section class="space-y-lg">
    <nav class="flex items-center gap-2 mb-md text-on-surface-variant">
        <a href="{{ route('resident.invoices') }}" class="hover:text-primary transition-colors flex items-center gap-1">
            <span class="material-symbols-outlined text-lg">arrow_back</span>
            <span class="text-body-sm">{{ __('messages.common.back_to_list') }}</span>
        </a>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-md">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface flex items-center gap-sm">
                <span class="material-symbols-outlined text-primary">description</span>
                {{ \Carbon\Carbon::create($bill->billing_year, $bill->billing_month, 1)->format('F Y') }}
            </h1>
            <p class="text-on-surface-variant mt-xs">{{ $apartment->number }} — {{ $apartment->owner_name }}</p>
        </div>
        <div>
            @switch($bill->status)
                @case('pending')
                    <span class="inline-flex items-center gap-xs px-md py-sm bg-tertiary-fixed text-tertiary text-body-md rounded-full font-bold">
                        <span class="w-2 h-2 bg-tertiary rounded-full"></span>
                        {{ __('messages.common.pending') }}
                    </span>
                    @break
                @case('paid')
                    <span class="inline-flex items-center gap-xs px-md py-sm bg-secondary/10 text-secondary text-body-md rounded-full font-bold">
                        <span class="w-2 h-2 bg-secondary rounded-full"></span>
                        {{ __('messages.common.paid') }}
                    </span>
                    @break
                @case('partial')
                    <span class="inline-flex items-center gap-xs px-md py-sm bg-orange-100 text-orange-700 text-body-md rounded-full font-bold">
                        <span class="w-2 h-2 bg-orange-500 rounded-full"></span>
                        {{ __('messages.resident.partial') }}
                    </span>
                    @break
                @case('overdue')
                    <span class="inline-flex items-center gap-xs px-md py-sm bg-[#FFEBE6] text-[#BF2600] text-body-md rounded-full font-bold">
                        <span class="w-2 h-2 bg-[#BF2600] rounded-full"></span>
                        {{ __('messages.common.overdue') }}
                    </span>
                    @break
                @default
                    <span class="inline-flex items-center gap-xs px-md py-sm bg-surface-container-low text-on-surface-variant text-body-md rounded-full font-bold">
                        {{ $bill->status }}
                    </span>
            @endswitch
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-md">
        <div class="bg-white rounded-xl p-md shadow-sm border border-outline-variant/20">
            <p class="font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ __('messages.resident.subtotal') }}</p>
            <p class="font-mono-data text-headline-md text-on-surface font-bold">RD${{ number_format($bill->subtotal, 2) }}</p>
        </div>
        @if($bill->previous_balance > 0)
        <div class="bg-[#FFEBE6] rounded-xl p-md border border-[#BF2600]/20">
            <p class="font-label-caps text-label-caps text-[#BF2600] mb-xs">{{ __('messages.resident.previous_balance') }}</p>
            <p class="font-mono-data text-headline-md text-[#BF2600] font-bold">RD${{ number_format($bill->previous_balance, 2) }}</p>
        </div>
        @else
        <div class="bg-white rounded-xl p-md shadow-sm border border-outline-variant/20">
            <p class="font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ __('messages.resident.previous_balance') }}</p>
            <p class="font-mono-data text-headline-md text-on-surface font-bold">RD$0.00</p>
        </div>
        @endif
        <div class="bg-primary rounded-xl p-md">
            <p class="font-label-caps text-white/80 mb-xs">{{ __('messages.common.total') }}</p>
            <p class="font-mono-data text-headline-lg text-white font-bold">RD${{ number_format($bill->total, 2) }}</p>
        </div>
    </div>

    {{-- Due Date --}}
    @if($bill->due_date)
    <div class="bg-white rounded-xl p-md shadow-sm border border-outline-variant/20 flex items-center gap-md">
        <span class="material-symbols-outlined text-primary text-2xl">event</span>
        <div>
            <p class="font-label-caps text-label-caps text-on-surface-variant">{{ __('messages.resident.due_date') }}</p>
            <p class="font-body-lg text-on-surface font-bold">{{ $bill->due_date->format('d/m/Y') }}</p>
        </div>
    </div>
    @endif

    {{-- Bill Items --}}
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/20 overflow-hidden">
        <div class="px-lg py-md bg-surface-container-low/50 border-b border-outline-variant/10">
            <h3 class="font-headline-md text-headline-md text-on-surface">{{ __('messages.resident.concepts') }}</h3>
        </div>
        <div class="divide-y divide-outline-variant/10">
            @foreach($bill->billItems as $item)
            <div class="px-lg py-md flex items-center gap-md">
                <span class="material-symbols-outlined text-primary text-xl">
                    @switch($item->concept_type)
                        @case('maintenance') home @break
                        @case('gas') local_gas_station @break
                        @case('extra_charge') add_card @break
                        @default receipt @endswitch
                </span>
                <div class="flex-1">
                    <p class="text-on-surface font-body-md">{{ $item->description }}</p>
                </div>
                <p class="font-mono-data text-on-surface font-bold">RD${{ number_format($item->amount, 2) }}</p>
            </div>
            @endforeach
        </div>
        <div class="px-lg py-md bg-surface-container-low/30 border-t-2 border-primary flex items-center justify-between">
            <span class="font-headline-md text-on-surface">{{ __('messages.common.total') }}</span>
            <span class="font-mono-data text-headline-lg text-primary font-bold">RD${{ number_format($bill->subtotal, 2) }}</span>
        </div>
    </div>

    {{-- Payments Applied --}}
    @if($bill->payments->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/20 overflow-hidden">
        <div class="px-lg py-md bg-surface-container-low/50 border-b border-outline-variant/10">
            <h3 class="font-headline-md text-headline-md text-on-surface">{{ __('messages.resident.payments_applied') }}</h3>
        </div>
        <div class="divide-y divide-outline-variant/10">
            @foreach($bill->payments as $payment)
            <div class="px-lg py-md flex items-center justify-between">
                <div class="flex items-center gap-md">
                    <span class="material-symbols-outlined text-lg @switch($payment->status) @case('confirmed') text-secondary @break @case('pending') text-tertiary @break @case('rejected') text-[#BF2600] @break @default text-on-surface-variant @endswitch">
                        @switch($payment->status)
                            @case('confirmed') check_circle @break
                            @case('pending') schedule @break
                            @case('rejected') cancel @break
                            @default help @endswitch
                    </span>
                    <div>
                        <p class="text-on-surface font-body-md">{{ $payment->payment_date->format('d/m/Y') }}</p>
                        @if($payment->reference_number)
                        <p class="text-body-sm text-on-surface-variant">Ref: {{ $payment->reference_number }}</p>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-mono-data text-on-surface font-bold">RD${{ number_format($payment->amount, 2) }}</p>
                    @switch($payment->status)
                        @case('confirmed')
                            <span class="text-[11px] font-bold text-[#006644] uppercase">{{ __('messages.common.confirmed') }}</span>
                            @break
                        @case('pending')
                            <span class="text-[11px] font-bold text-tertiary uppercase">{{ __('messages.common.pending') }}</span>
                            @break
                        @case('rejected')
                            <span class="text-[11px] font-bold text-[#BF2600] uppercase">{{ __('messages.common.rejected') }}</span>
                            @break
                    @endswitch
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Action Button --}}
    @if($bill->status === 'pending' || $bill->status === 'overdue' || $bill->status === 'partial')
    <div class="flex justify-center pt-md">
        <a href="{{ route('resident.vouchers.upload') }}" class="flex items-center gap-sm bg-primary text-on-primary px-xl py-md rounded-lg font-bold shadow-sm hover:bg-primary-container hover:text-on-primary-container transition-colors active:scale-[0.98]">
            <span class="material-symbols-outlined">cloud_upload</span>
            {{ __('messages.resident.upload_voucher_title') }}
        </a>
    </div>
    @endif
</section>
@endsection