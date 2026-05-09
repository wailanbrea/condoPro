@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <a href="{{ route('condominiums.index') }}" class="text-body-sm hover:text-primary transition-colors">{{ __('messages.condominiums.title') }}</a>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ __('messages.condominiums.edit_title') }}</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">{{ __('messages.condominiums.edit_title') }}</h2>
        <p class="text-on-surface-variant">{{ $condominium->name }}</p>
    </div>
    <a href="{{ route('condominiums.index') }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
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
    <form action="{{ route('condominiums.update', $condominium) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="name">{{ __('messages.condominiums.name') }} <span class="text-error">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $condominium->name) }}" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('name') border-error ring-1 ring-error @enderror" />
                @error('name')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="phone">{{ __('messages.condominiums.phone') }}</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $condominium->phone) }}"
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('phone') border-error ring-1 ring-error @enderror" />
                @error('phone')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="address">{{ __('messages.condominiums.address') }} <span class="text-error">*</span></label>
                <textarea id="address" name="address" rows="2" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all resize-none @error('address') border-error ring-1 ring-error @enderror">{{ old('address', $condominium->address) }}</textarea>
                @error('address')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="email">{{ __('messages.condominiums.email') }}</label>
                <input type="email" id="email" name="email" value="{{ old('email', $condominium->email) }}"
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('email') border-error ring-1 ring-error @enderror" />
                @error('email')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="currency">{{ __('messages.condominiums.currency') }}</label>
                <select id="currency" name="currency"
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('currency') border-error ring-1 ring-error @enderror">
                    <option value="DOP" {{ old('currency', $condominium->currency) === 'DOP' ? 'selected' : '' }}>{{ __('messages.condominiums.dop') }}</option>
                    <option value="USD" {{ old('currency', $condominium->currency) === 'USD' ? 'selected' : '' }}>{{ __('messages.condominiums.usd') }}</option>
                </select>
                @error('currency')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="language_default">{{ __('messages.condominiums.language_default') }}</label>
                <select id="language_default" name="language_default"
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('language_default') border-error ring-1 ring-error @enderror">
                    <option value="es" {{ old('language_default', $condominium->language_default) === 'es' ? 'selected' : '' }}>{{ __('messages.condominiums.spanish') }}</option>
                    <option value="en" {{ old('language_default', $condominium->language_default) === 'en' ? 'selected' : '' }}>{{ __('messages.condominiums.english') }}</option>
                </select>
                @error('language_default')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="status">{{ __('messages.condominiums.status') }}</label>
                <select id="status" name="status"
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('status') border-error ring-1 ring-error @enderror">
                    <option value="active" {{ old('status', $condominium->status) === 'active' ? 'selected' : '' }}>{{ __('messages.common.active') }}</option>
                    <option value="inactive" {{ old('status', $condominium->status) === 'inactive' ? 'selected' : '' }}>{{ __('messages.common.inactive') }}</option>
                </select>
                @error('status')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-xl pt-lg border-t border-outline-variant">
            <h3 class="font-headline-md text-headline-md text-on-surface mb-md flex items-center gap-sm">
                <span class="material-symbols-outlined text-primary" data-icon="local_gas_station">local_gas_station</span>
                Configuración de Gas
            </h3>
            <p class="text-body-sm text-on-surface-variant mb-lg">Estos valores se usarán como predeterminados al registrar lecturas de gas y se mostrarán en las facturas.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
                <div>
                    <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="gas_price_per_gallon">Precio por Galón (RD$) <span class="text-on-surface-variant text-body-sm font-normal">(predeterminado)</span></label>
                    <div class="relative">
                        <span class="absolute left-md top-1/2 -translate-y-1/2 font-mono-data text-on-surface-variant">RD$</span>
                        <input type="number" id="gas_price_per_gallon" name="gas_price_per_gallon" value="{{ old('gas_price_per_gallon', $condominium->gas_price_per_gallon) }}" step="0.01" min="0"
                            class="w-full pl-xl pr-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface font-mono-data focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('gas_price_per_gallon') border-error ring-1 ring-error @enderror" placeholder="749.00" />
                    </div>
                    @error('gas_price_per_gallon')
                        <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="gas_conversion_factor">Factor de Conversión (m³ → gal) <span class="text-on-surface-variant text-body-sm font-normal">(predeterminado)</span></label>
                    <input type="number" id="gas_conversion_factor" name="gas_conversion_factor" value="{{ old('gas_conversion_factor', $condominium->gas_conversion_factor) }}" step="0.0001" min="0"
                        class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface font-mono-data focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('gas_conversion_factor') border-error ring-1 ring-error @enderror" placeholder="0.1781" />
                    @error('gas_conversion_factor')
                        <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-body-sm text-on-surface-variant">Fórmula: Galones = Consumo (m³) × Factor. Valor típico RD: 0.1781</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-md mt-xl pt-lg border-t border-outline-variant">
            <button type="submit" class="px-lg py-2 bg-primary text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all">
                <span class="material-symbols-outlined" data-icon="save">save</span>
                {{ __('messages.common.save') }}
            </button>
            <a href="{{ route('condominiums.index') }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
                {{ __('messages.common.cancel') }}
            </a>
        </div>
    </form>
</div>
@endsection