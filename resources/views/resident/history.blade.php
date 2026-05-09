@extends('layouts.resident')

@section('content')
<section class="space-y-lg">
    {{-- Header --}}
    <div class="flex items-center gap-sm">
        <span class="material-symbols-outlined text-primary text-3xl">history</span>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">{{ __('messages.resident.payment_history') }}</h1>
    </div>

    {{-- Payments Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        @if ($payments->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low/50">
                            <th class="px-lg py-md font-label-caps text-label-caps text-on-surface-variant">{{ __('messages.common.date') }}</th>
                            <th class="px-lg py-md font-label-caps text-label-caps text-on-surface-variant">{{ __('messages.common.concept') }}</th>
                            <th class="px-lg py-md font-label-caps text-label-caps text-on-surface-variant">{{ __('messages.common.amount') }}</th>
                            <th class="px-lg py-md font-label-caps text-label-caps text-on-surface-variant">{{ __('messages.payments.voucher') }}</th>
                            <th class="px-lg py-md font-label-caps text-label-caps text-on-surface-variant">{{ __('messages.common.status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        @foreach ($payments as $payment)
                            <tr class="hover:bg-surface-container-low transition-colors group">
                                <td class="px-lg py-md font-body-md text-on-surface whitespace-nowrap">{{ $payment->payment_date->format('d M, Y') }}</td>
                                <td class="px-lg py-md">
                                    <div class="flex flex-col">
                                        <span class="font-body-md font-bold">{{ $payment->bill ? $payment->bill->billItems->first()?->description ?? __('messages.common.concept') : __('messages.common.concept') }}</span>
                                        @if($payment->reference_number)
                                            <span class="text-xs text-on-surface-variant">{{ __('messages.common.reference') }}: #{{ $payment->reference_number }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-lg py-md font-mono-data text-on-surface">RD${{ number_format($payment->amount, 2) }}</td>
                                <td class="px-lg py-md">
                                    @if($payment->voucher_path)
                                        @php $ext = strtolower(pathinfo($payment->voucher_path, PATHINFO_EXTENSION)); @endphp
                                        @if(in_array($ext, ['jpg', 'jpeg', 'png']))
                                            <img src="{{ \Storage::url($payment->voucher_path) }}" alt="Voucher" class="h-10 w-10 rounded object-cover cursor-pointer hover:opacity-80 transition-opacity" onclick="document.getElementById('voucherModal-{{ $payment->id }}').classList.remove('hidden')">
                                            <div id="voucherModal-{{ $payment->id }}" class="hidden fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-lg" onclick="this.classList.add('hidden')">
                                                <div class="relative max-w-3xl">
                                                    <button onclick="event.stopPropagation(); document.getElementById('voucherModal-{{ $payment->id }}').classList.add('hidden')" class="absolute -top-3 -right-3 bg-white text-on-surface rounded-full w-8 h-8 flex items-center justify-center shadow-lg z-10">
                                                        <span class="material-symbols-outlined text-lg">close</span>
                                                    </button>
                                                    <img src="{{ \Storage::url($payment->voucher_path) }}" alt="Voucher" class="max-w-full max-h-[85vh] rounded-lg shadow-2xl" onclick="event.stopPropagation()">
                                                </div>
                                            </div>
                                        @elseif($ext === 'pdf')
                                            <a href="{{ \Storage::url($payment->voucher_path) }}" target="_blank" class="flex items-center gap-xs text-primary hover:underline">
                                                <span class="material-symbols-outlined text-lg">picture_as_pdf</span>
                                                <span class="text-body-sm">PDF</span>
                                            </a>
                                        @else
                                            <a href="{{ \Storage::url($payment->voucher_path) }}" target="_blank" class="flex items-center gap-xs text-primary hover:underline">
                                                <span class="material-symbols-outlined text-lg">download</span>
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-on-surface-variant text-body-sm">—</span>
                                    @endif
                                </td>
                                <td class="px-lg py-md">
                                    @switch($payment->status)
                                        @case('confirmed')
                                            <span class="inline-flex items-center gap-xs px-sm py-xs bg-secondary/10 text-secondary text-body-sm rounded-full font-semibold">
                                                <span class="w-2 h-2 bg-secondary rounded-full"></span>
                                                {{ __('messages.common.confirmed') }}
                                            </span>
                                            @break
                                        @case('pending')
                                            <span class="inline-flex items-center gap-xs px-sm py-xs bg-tertiary-fixed text-tertiary text-body-sm rounded-full font-semibold">
                                                <span class="w-2 h-2 bg-tertiary rounded-full"></span>
                                                {{ __('messages.common.pending') }}
                                            </span>
                                            @break
                                        @case('rejected')
                                            <span class="inline-flex items-center gap-xs px-sm py-xs bg-[#FFEBE6] text-[#BF2600] text-body-sm rounded-full font-semibold">
                                                <span class="w-2 h-2 bg-[#BF2600] rounded-full"></span>
                                                {{ __('messages.common.rejected') }}
                                            </span>
                                            @break
                                        @case('paid')
                                            <span class="inline-flex items-center gap-xs px-sm py-xs bg-secondary/10 text-secondary text-body-sm rounded-full font-semibold">
                                                <span class="w-2 h-2 bg-secondary rounded-full"></span>
                                                {{ __('messages.common.paid') }}
                                            </span>
                                            @break
                                        @default
                                            <span class="inline-flex items-center gap-xs px-sm py-xs bg-surface-container-low text-on-surface-variant text-body-sm rounded-full font-semibold">
                                                {{ $payment->status }}
                                            </span>
                                    @endswitch
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-xl text-center space-y-sm">
                <span class="material-symbols-outlined text-5xl text-on-surface-variant/40">history</span>
                <p class="font-body-lg text-on-surface-variant">{{ __('messages.resident.no_payments') }}</p>
            </div>
        @endif
    </div>
</section>
@endsection