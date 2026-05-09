@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <a href="{{ route('payments.index') }}" class="text-body-sm hover:text-primary transition-colors">{{ __('messages.payments.title') }}</a>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ __('messages.common.edit') }}</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">{{ __('messages.common.edit') }} {{ __('messages.payments.title') }}</h2>
        <p class="text-on-surface-variant">{{ $payment->apartment->number ?? '—' }} — {{ number_format($payment->amount, 2) }}</p>
    </div>
    <a href="{{ route('payments.index') }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
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

<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg max-w-3xl">
    <form action="{{ route('payments.update', $payment) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="condominium_id">{{ __('messages.condominiums.title') }} <span class="text-error">*</span></label>
                <select id="condominium_id" name="condominium_id" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('condominium_id') border-error ring-1 ring-error @enderror">
                    <option value="">{{ __('messages.condominiums.title') }}</option>
                    @foreach($condominiums as $id => $name)
                        <option value="{{ $id }}" {{ old('condominium_id', $payment->condominium_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @error('condominium_id')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="apartment_id">{{ __('messages.payments.apartment') }} <span class="text-error">*</span></label>
                <select id="apartment_id" name="apartment_id" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('apartment_id') border-error ring-1 ring-error @enderror">
                    <option value="">{{ __('messages.payments.apartment') }}</option>
                    @foreach($apartments as $id => $name)
                        <option value="{{ $id }}" {{ old('apartment_id', $payment->apartment_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @error('apartment_id')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="bank_account_id">{{ __('messages.resident.bank_account') }} <span class="text-error">*</span></label>
                <select id="bank_account_id" name="bank_account_id" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('bank_account_id') border-error ring-1 ring-error @enderror">
                    <option value="">{{ __('messages.resident.bank_account') }}</option>
                    @foreach($bankAccounts as $id => $name)
                        <option value="{{ $id }}" {{ old('bank_account_id', $payment->bank_account_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @error('bank_account_id')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="amount">{{ __('messages.payments.amount') }} <span class="text-error">*</span></label>
                <input type="number" id="amount" name="amount" value="{{ old('amount', $payment->amount) }}" step="0.01" min="0" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('amount') border-error ring-1 ring-error @enderror" />
                @error('amount')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="payment_date">{{ __('messages.payments.payment_date') }} <span class="text-error">*</span></label>
                <input type="date" id="payment_date" name="payment_date" value="{{ old('payment_date', $payment->payment_date ? $payment->payment_date->format('Y-m-d') : '') }}" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('payment_date') border-error ring-1 ring-error @enderror" />
                @error('payment_date')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="reference_number">{{ __('messages.payments.reference_number') }}</label>
                <input type="text" id="reference_number" name="reference_number" value="{{ old('reference_number', $payment->reference_number) }}"
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('reference_number') border-error ring-1 ring-error @enderror" />
                @error('reference_number')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="voucher_path">{{ __('messages.payments.voucher') }}</label>
                @if($payment->voucher_path)
                    <p class="text-body-sm text-secondary mb-1">Archivo actual: <a href="{{ \Storage::url($payment->voucher_path) }}" class="underline" target="_blank">{{ basename($payment->voucher_path) }}</a></p>
                @endif
                <input type="file" id="voucher_path" name="voucher_path" accept=".pdf,.jpg,.jpeg,.png"
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('voucher_path') border-error ring-1 ring-error @enderror" />
                @error('voucher_path')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex items-center gap-md mt-xl pt-lg border-t border-outline-variant">
            <button type="submit" class="px-lg py-2 bg-primary text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all">
                <span class="material-symbols-outlined" data-icon="save">save</span>
                {{ __('messages.common.save') }}
            </button>
            <a href="{{ route('payments.index') }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
                {{ __('messages.common.cancel') }}
            </a>
        </div>
    </form>
</div>
@endsection