@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <a href="{{ route('payments.index') }}" class="text-body-sm hover:text-primary transition-colors">{{ __('messages.payments.title') }}</a>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ __('messages.common.details') }}</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">{{ __('messages.payments.title') }} — {{ __('messages.common.details') }}</h2>
        <p class="text-on-surface-variant">{{ $payment->apartment->number ?? '—' }} — {{ $payment->user->name ?? '—' }}</p>
    </div>
    <div class="flex gap-md">
        @if($payment->status === 'pending')
            <form action="{{ route('payments.confirm', $payment) }}" method="POST" onsubmit="return confirm('{{ __('messages.payments.confirm_question') }}')">
                @csrf
                <button type="submit" class="px-lg py-2 bg-[#006644] text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all">
                    <span class="material-symbols-outlined" data-icon="check_circle">check_circle</span>
                    {{ __('messages.payments.confirm') }}
                </button>
            </form>
            <form action="{{ route('payments.reject', $payment) }}" method="POST" onsubmit="return confirm('{{ __('messages.payments.reject_question') }}')">
                @csrf
                <button type="submit" class="px-lg py-2 bg-[#BF2600] text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all">
                    <span class="material-symbols-outlined" data-icon="cancel">cancel</span>
                    {{ __('messages.payments.reject') }}
                </button>
            </form>
        @endif
        <a href="{{ route('payments.index') }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
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

@if(session('error'))
    <div class="mb-lg px-lg py-md bg-[#FFEBE6] text-[#BF2600] rounded-lg flex items-center gap-2 border border-[#BF2600]/20">
        <span class="material-symbols-outlined" data-icon="error">error</span>
        {{ session('error') }}
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter mb-xl">
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-primary">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.payments.amount') }}</p>
        <h3 class="font-headline-lg text-headline-lg text-primary">{{ number_format($payment->amount, 2) }}</h3>
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-secondary">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.payments.apartment') }}</p>
        <h3 class="font-headline-md text-headline-md text-secondary">{{ $payment->apartment->number ?? '—' }}</h3>
        <p class="text-body-sm text-on-surface-variant">{{ $payment->apartment->owner_name ?? '—' }}</p>
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-outline">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.common.status') }}</p>
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
            @case('cancelled')
                <span class="px-3 py-1 bg-surface-container-low text-on-surface-variant rounded-full text-[11px] font-bold uppercase tracking-wider">{{ __('messages.common.cancelled') }}</span>
                @break
            @default
                <span class="px-3 py-1 bg-surface-container-low text-on-surface-variant rounded-full text-[11px] font-bold uppercase tracking-wider">{{ $payment->status }}</span>
        @endswitch
    </div>
</div>

<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg mb-xl">
    <h4 class="font-headline-md text-headline-md mb-lg">{{ __('messages.common.details') }}</h4>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
        <div>
            <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.payments.user') }}</p>
            <p class="text-on-surface">{{ $payment->user->name ?? '—' }}</p>
        </div>
        <div>
            <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.payments.payment_date') }}</p>
            <p class="text-on-surface">{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : '—' }}</p>
        </div>
        <div>
            <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.resident.bank_account') }}</p>
            <p class="text-on-surface">{{ $payment->bankAccount->bank_name ?? '—' }} — {{ $payment->bankAccount->account_number ?? '—' }}</p>
        </div>
        <div>
            <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.payments.reference_number') }}</p>
            <p class="text-on-surface">{{ $payment->reference_number ?? '—' }}</p>
        </div>
        @if($payment->voucher_path)
        <div class="md:col-span-2">
            <p class="text-on-surface-variant font-label-caps text-label-caps mb-sm">{{ __('messages.payments.voucher') }}</p>
            @php
                $ext = strtolower(pathinfo($payment->voucher_path, PATHINFO_EXTENSION));
                $isImage = in_array($ext, ['jpg', 'jpeg', 'png']);
            @endphp
            @if($isImage)
                <div class="relative group inline-block">
                    <img src="{{ \Storage::url($payment->voucher_path) }}" 
                         alt="{{ __('messages.payments.voucher') }}"
                         class="max-w-full max-h-96 rounded-xl border border-outline-variant/30 shadow-sm cursor-zoom-in transition-shadow group-hover:shadow-lg"
                         onclick="document.getElementById('voucherModal').classList.remove('hidden')">
                    <div class="absolute bottom-2 right-2 bg-black/60 text-white px-2 py-1 rounded-lg text-xs flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">zoom_in</span>
                        Ampliar
                    </div>
                </div>
                <div id="voucherModal" class="hidden fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-lg" onclick="this.classList.add('hidden')">
                    <div class="relative max-w-5xl max-h-[95vh]">
                        <button onclick="event.stopPropagation(); document.getElementById('voucherModal').classList.add('hidden')" class="absolute -top-3 -right-3 bg-white text-on-surface rounded-full w-8 h-8 flex items-center justify-center shadow-lg hover:bg-surface-container-high transition-colors z-10">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                        <img src="{{ \Storage::url($payment->voucher_path) }}" alt="{{ __('messages.payments.voucher') }}" class="max-w-full max-h-[90vh] rounded-lg shadow-2xl" onclick="event.stopPropagation()">
                    </div>
                </div>
            @elseif($ext === 'pdf')
                <div class="border border-outline-variant/30 rounded-xl overflow-hidden">
                    <iframe src="{{ \Storage::url($payment->voucher_path) }}" class="w-full h-96 border-0"></iframe>
                    <div class="px-md py-sm bg-surface-container-low border-t border-outline-variant/20 flex items-center justify-between">
                        <span class="text-body-sm text-on-surface-variant flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">picture_as_pdf</span>
                            {{ basename($payment->voucher_path) }}
                        </span>
                        <a href="{{ \Storage::url($payment->voucher_path) }}" target="_blank" class="text-primary hover:underline text-body-sm font-bold flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">open_in_new</span>
                            Abrir en nueva pestaña
                        </a>
                    </div>
                </div>
            @else
                <a href="{{ \Storage::url($payment->voucher_path) }}" target="_blank" class="text-primary hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">download</span>
                    {{ basename($payment->voucher_path) }}
                </a>
            @endif
        </div>
        @endif
        @if($payment->admin_observation)
        <div class="md:col-span-2">
            <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.common.details') }}</p>
            <p class="text-on-surface">{{ $payment->admin_observation }}</p>
        </div>
        @endif
        @if($payment->confirmed_by)
        <div>
            <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.common.confirmed') }} {{ __('messages.common.by') }}</p>
            <p class="text-on-surface">{{ $payment->confirmer->name ?? '—' }}</p>
        </div>
        @endif
        @if($payment->confirmed_at)
        <div>
            <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.common.confirmed') }} {{ __('messages.common.date') }}</p>
            <p class="text-on-surface">{{ $payment->confirmed_at->format('d/m/Y H:i') }}</p>
        </div>
        @endif
    </div>
</div>
@endsection