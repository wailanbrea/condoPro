@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <a href="{{ route('bank-accounts.index') }}" class="text-body-sm hover:text-primary transition-colors">Cuentas Bancarias</a>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">Nueva Cuenta</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">Nueva Cuenta Bancaria</h2>
        <p class="text-on-surface-variant">Registrar una cuenta bancaria</p>
    </div>
    <a href="{{ route('bank-accounts.index') }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
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
    <form action="{{ route('bank-accounts.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="bank_name">Banco <span class="text-error">*</span></label>
                <input type="text" id="bank_name" name="bank_name" value="{{ old('bank_name') }}" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('bank_name') border-error ring-1 ring-error @enderror" />
                @error('bank_name')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="account_holder">Titular <span class="text-error">*</span></label>
                <input type="text" id="account_holder" name="account_holder" value="{{ old('account_holder') }}" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('account_holder') border-error ring-1 ring-error @enderror" />
                @error('account_holder')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="account_number">Número de Cuenta <span class="text-error">*</span></label>
                <input type="text" id="account_number" name="account_number" value="{{ old('account_number') }}" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('account_number') border-error ring-1 ring-error @enderror" />
                @error('account_number')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="account_type">Tipo de Cuenta <span class="text-error">*</span></label>
                <select id="account_type" name="account_type" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('account_type') border-error ring-1 ring-error @enderror">
                    <option value="savings" {{ old('account_type') === 'savings' ? 'selected' : '' }}>Ahorro</option>
                    <option value="checking" {{ old('account_type') === 'checking' ? 'selected' : '' }}>Corriente</option>
                </select>
                @error('account_type')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="currency">Moneda <span class="text-error">*</span></label>
                <select id="currency" name="currency" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('currency') border-error ring-1 ring-error @enderror">
                    <option value="DOP" {{ old('currency', 'DOP') === 'DOP' ? 'selected' : '' }}>Peso Dominicano (DOP)</option>
                    <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>Dólar Estadounidense (USD)</option>
                </select>
                @error('currency')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="status">{{ __('messages.common.status') }}</label>
                <select id="status" name="status"
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('status') border-error ring-1 ring-error @enderror">
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>{{ __('messages.common.active') }}</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>{{ __('messages.common.inactive') }}</option>
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
            <a href="{{ route('bank-accounts.index') }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
                {{ __('messages.common.cancel') }}
            </a>
        </div>
    </form>
</div>
@endsection