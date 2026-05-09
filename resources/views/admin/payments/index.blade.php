@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ __('messages.payments.title') }}</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">{{ __('messages.payments.title') }}</h2>
        <p class="text-on-surface-variant">{{ __('messages.payments.list') }}</p>
    </div>
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
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.payments.apartment') }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.payments.user') }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.payments.amount') }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.payments.payment_date') }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.payments.voucher') }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.common.status') }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-container-low">
                @forelse($payments as $payment)
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-lg py-md font-bold text-on-surface">{{ $payment->apartment->number ?? '—' }}</td>
                        <td class="px-lg py-md">{{ $payment->user->name ?? '—' }}</td>
                        <td class="px-lg py-md font-mono-data text-on-surface">{{ number_format($payment->amount, 2) }}</td>
                        <td class="px-lg py-md text-body-sm">{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : '—' }}</td>
                        <td class="px-lg py-md">
                            @if($payment->voucher_path)
                                @php $ext = strtolower(pathinfo($payment->voucher_path, PATHINFO_EXTENSION)); @endphp
                                @if(in_array($ext, ['jpg', 'jpeg', 'png']))
                                    <a href="{{ route('payments.show', $payment) }}" class="block">
                                        <img src="{{ \Storage::url($payment->voucher_path) }}" alt="Voucher" class="h-8 w-12 rounded object-cover hover:opacity-80 transition-opacity">
                                    </a>
                                @elseif($ext === 'pdf')
                                    <a href="{{ route('payments.show', $payment) }}" class="flex items-center gap-1 text-primary hover:underline">
                                        <span class="material-symbols-outlined text-lg">picture_as_pdf</span>
                                        <span class="text-body-sm">PDF</span>
                                    </a>
                                @else
                                    <a href="{{ route('payments.show', $payment) }}" class="text-primary hover:underline text-body-sm">Ver</a>
                                @endif
                            @else
                                <span class="text-on-surface-variant text-body-sm">—</span>
                            @endif
                        </td>
                        <td class="px-lg py-md">
                            @switch($payment->status)
                                @case('pending')
                                    <span class="px-3 py-1 bg-tertiary-fixed text-tertiary rounded-full text-[11px] font-bold uppercase tracking-wider">{{ __('messages.common.pending') }}</span>
                                    @break
                                @case('confirmed')
                                    <span class="px-3 py-1 bg-[#E3FCEF] text-[#006644] rounded-full text-[11px] font-bold uppercase tracking-wider">{{ __('messages.common.confirmed') }}</span>
                                    @break
                                @case('rejected')
                                    <span class="px-3 py-1 bg-[#FFEBE6] text-[#BF2600] rounded-full text-[11px] font-bold uppercase tracking-wider">{{ __('messages.common.rejected') }}</span>
                                    @break
                                @case('paid')
                                    <span class="px-3 py-1 bg-secondary-container text-on-secondary rounded-full text-[11px] font-bold uppercase tracking-wider">{{ __('messages.common.paid') }}</span>
                                    @break
                                @default
                                    <span class="px-3 py-1 bg-surface-container-low text-on-surface-variant rounded-full text-[11px] font-bold uppercase tracking-wider">{{ $payment->status }}</span>
                            @endswitch
                        </td>
                        <td class="px-lg py-md">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('payments.show', $payment) }}" class="p-1 hover:bg-surface-container-high rounded-lg text-on-surface-variant hover:text-primary transition-colors" title="{{ __('messages.common.view') }}">
                                    <span class="material-symbols-outlined text-lg" data-icon="visibility">visibility</span>
                                </a>
                                @if($payment->status === 'pending')
                                    <form action="{{ route('payments.confirm', $payment) }}" method="POST" onsubmit="return confirm('{{ __('messages.payments.confirm_question') }}')">
                                        @csrf
                                        <button type="submit" class="p-1 hover:bg-surface-container-high rounded-lg text-secondary hover:text-on-secondary transition-colors" title="{{ __('messages.payments.confirm') }}">
                                            <span class="material-symbols-outlined text-lg" data-icon="check_circle">check_circle</span>
                                        </button>
                                    </form>
                                    <form action="{{ route('payments.reject', $payment) }}" method="POST" onsubmit="return confirm('{{ __('messages.payments.reject_question') }}')">
                                        @csrf
                                        <button type="submit" class="p-1 hover:bg-surface-container-high rounded-lg text-on-surface-variant hover:text-error transition-colors" title="{{ __('messages.payments.reject') }}">
                                            <span class="material-symbols-outlined text-lg" data-icon="cancel">cancel</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-lg py-xl text-center text-on-surface-variant">{{ __('messages.common.no_results') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection