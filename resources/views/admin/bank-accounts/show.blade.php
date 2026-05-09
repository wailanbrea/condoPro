@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <a href="{{ route('bank-accounts.index') }}" class="text-body-sm hover:text-primary transition-colors">Cuentas Bancarias</a>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ $bankAccount->bank_name }}</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">{{ $bankAccount->bank_name }}</h2>
        <p class="text-on-surface-variant">{{ $bankAccount->account_number }}</p>
    </div>
    <div class="flex gap-md">
        <a href="{{ route('bank-accounts.edit', $bank_account) }}" class="px-lg py-2 bg-primary text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all">
            <span class="material-symbols-outlined" data-icon="edit">edit</span>
            {{ __('messages.common.edit') }}
        </a>
        <a href="{{ route('bank-accounts.index') }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
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
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">Banco</p>
        <h3 class="font-headline-lg text-headline-lg text-primary">{{ $bankAccount->bank_name }}</h3>
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-secondary">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">Número de Cuenta</p>
        <h3 class="font-headline-md text-headline-md text-secondary font-mono-data">{{ $bankAccount->account_number }}</h3>
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-outline">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.common.status') }}</p>
        @switch($bankAccount->status)
            @case('active')
                <span class="px-3 py-1 bg-[#E3FCEF] text-[#006644] rounded-full text-[11px] font-bold uppercase tracking-wider">{{ __('messages.common.active') }}</span>
                @break
            @case('inactive')
                <span class="px-3 py-1 bg-[#FFEBE6] text-[#BF2600] rounded-full text-[11px] font-bold uppercase tracking-wider">{{ __('messages.common.inactive') }}</span>
                @break
            @default
                <span class="px-3 py-1 bg-surface-container-low text-on-surface-variant rounded-full text-[11px] font-bold uppercase tracking-wider">{{ $bankAccount->status }}</span>
        @endswitch
    </div>
</div>

<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg mb-xl">
    <h4 class="font-headline-md text-headline-md mb-lg">{{ __('messages.common.details') }}</h4>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
        <div>
            <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">Titular</p>
            <p class="text-on-surface">{{ $bankAccount->account_holder }}</p>
        </div>
        <div>
            <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">Tipo de Cuenta</p>
            <p class="text-on-surface">{{ $bankAccount->account_type === 'savings' ? 'Ahorro' : 'Corriente' }}</p>
        </div>
        <div>
            <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.condominiums.currency') }}</p>
            <p class="text-on-surface">{{ $bankAccount->currency }}</p>
        </div>
        <div>
            <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.condominiums.title') }}</p>
            <p class="text-on-surface">{{ $bankAccount->condominium->name ?? '—' }}</p>
        </div>
    </div>
</div>

@if($bankAccount->relationLoaded('payments') && $bankAccount->payments->count() > 0)
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] overflow-hidden">
    <div class="p-lg border-b border-surface-container-low">
        <h4 class="font-headline-md text-headline-md">{{ __('messages.payments.title') }}</h4>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-low/50">
                <tr>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.payments.apartment') }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.payments.amount') }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.payments.payment_date') }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.common.status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-container-low">
                @foreach($bankAccount->payments as $payment)
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-lg py-md font-bold text-on-surface">{{ $payment->apartment->number ?? '—' }}</td>
                        <td class="px-lg py-md font-mono-data text-mono-data">{{ number_format($payment->amount, 2) }}</td>
                        <td class="px-lg py-md text-on-surface-variant">{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : '—' }}</td>
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
                                @default
                                    <span class="px-3 py-1 bg-surface-container-low text-on-surface-variant rounded-full text-[11px] font-bold uppercase tracking-wider">{{ $payment->status }}</span>
                            @endswitch
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection