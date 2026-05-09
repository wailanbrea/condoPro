@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <a href="{{ route('billing.index') }}" class="text-body-sm hover:text-primary transition-colors">{{ __('messages.billing.title') }}</a>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ __('messages.common.edit') }}</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">Editar Factura</h2>
        <p class="text-on-surface-variant">{{ $billing->apartment->number ?? '—' }} — {{ \Carbon\Carbon::create()->month($billing->billing_month)->translatedFormat('F') }} {{ $billing->billing_year }}</p>
    </div>
    <a href="{{ route('billing.show', $billing) }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
        <span class="material-symbols-outlined" data-icon="arrow_back">arrow_back</span>
        {{ __('messages.common.back_to_list') }}
    </a>
</div>

@if($errors->any())
    <div class="mb-lg px-lg py-md bg-[#FFEBE6] text-[#BF2600] rounded-lg border border-[#BF2600]/20">
        <div class="flex items-center gap-2 mb-sm">
            <span class="material-symbols-outlined" data-icon="error">error</span>
            <span class="font-bold">{{ __('messages.common.error_message') }}</span>
        </div>
        <ul class="list-disc list-inside text-body-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

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

<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg max-w-3xl mb-xl">
    <form action="{{ route('billing.update', $billing) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1">{{ __('messages.common.apartment') }}</label>
                <input type="text" disabled value="{{ $billing->apartment->number ?? '—' }}"
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface-variant cursor-not-allowed" />
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1">Período</label>
                <input type="text" disabled value="{{ \Carbon\Carbon::create()->month($billing->billing_month)->translatedFormat('F') }} {{ $billing->billing_year }}"
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface-variant cursor-not-allowed" />
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="due_date">{{ __('messages.billing.due_date') }} <span class="text-error">*</span></label>
                <input type="date" id="due_date" name="due_date" value="{{ old('due_date', $billing->due_date ? $billing->due_date->format('Y-m-d') : '') }}" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('due_date') border-error ring-1 ring-error @enderror" />
                @error('due_date')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="status">{{ __('messages.common.status') }} <span class="text-error">*</span></label>
                <select id="status" name="status" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('status') border-error ring-1 ring-error @enderror">
                    <option value="pending" {{ old('status', $billing->status) === 'pending' ? 'selected' : '' }}>{{ __('messages.common.pending') }}</option>
                    <option value="paid" {{ old('status', $billing->status) === 'paid' ? 'selected' : '' }}>{{ __('messages.common.paid') }}</option>
                    <option value="overdue" {{ old('status', $billing->status) === 'overdue' ? 'selected' : '' }}>{{ __('messages.common.overdue') }}</option>
                    <option value="cancelled" {{ old('status', $billing->status) === 'cancelled' ? 'selected' : '' }}>{{ __('messages.common.cancelled') }}</option>
                </select>
                @error('status')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex items-center gap-md mt-xl pt-lg border-t border-outline-variant">
            <button type="submit" class="px-lg py-2 bg-primary text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all">
                <span class="material-symbols-outlined" data-icon="save">save</span>
                {{ __('messages.common.save') }}
            </button>
            <a href="{{ route('billing.show', $billing) }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
                {{ __('messages.common.cancel') }}
            </a>
        </div>
    </form>
</div>

<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg mb-xl">
    <h4 class="font-headline-md text-headline-md mb-md">Resumen Financiero</h4>
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

<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] overflow-hidden">
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
@endsection