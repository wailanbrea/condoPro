@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <a href="{{ route('apartments.index') }}" class="text-body-sm hover:text-primary transition-colors">{{ __('messages.apartments.title') }}</a>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ __('messages.apartments.edit_title') }}</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">{{ __('messages.apartments.edit_title') }}</h2>
        <p class="text-on-surface-variant">{{ $apartment->number }}</p>
    </div>
    <a href="{{ route('apartments.index') }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
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
    <form action="{{ route('apartments.update', $apartment) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="number">{{ __('messages.apartments.number') }} <span class="text-error">*</span></label>
                <input type="text" id="number" name="number" value="{{ old('number', $apartment->number) }}" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('number') border-error ring-1 ring-error @enderror" />
                @error('number')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="owner_name">{{ __('messages.apartments.owner_name') }}</label>
                <input type="text" id="owner_name" name="owner_name" value="{{ old('owner_name', $apartment->owner_name) }}"
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('owner_name') border-error ring-1 ring-error @enderror" />
                @error('owner_name')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="condominium_id">{{ __('messages.apartments.condominium_id') }} <span class="text-error">*</span></label>
                <select id="condominium_id" name="condominium_id" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('condominium_id') border-error ring-1 ring-error @enderror">
                    <option value="">{{ __('messages.apartments.condominium_id') }}</option>
                    @foreach($condominiums as $id => $name)
                        <option value="{{ $id }}" {{ old('condominium_id', $apartment->condominium_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @error('condominium_id')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="maintenance_fee">{{ __('messages.apartments.maintenance_fee') }}</label>
                <input type="number" id="maintenance_fee" name="maintenance_fee" value="{{ old('maintenance_fee', $apartment->maintenance_fee) }}" step="0.01" min="0"
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('maintenance_fee') border-error ring-1 ring-error @enderror" />
                @error('maintenance_fee')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="status">{{ __('messages.apartments.status') }}</label>
                <select id="status" name="status"
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('status') border-error ring-1 ring-error @enderror">
                    <option value="active" {{ old('status', $apartment->status) === 'active' ? 'selected' : '' }}>{{ __('messages.common.active') }}</option>
                    <option value="inactive" {{ old('status', $apartment->status) === 'inactive' ? 'selected' : '' }}>{{ __('messages.common.inactive') }}</option>
                </select>
                @error('status')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center pt-lg">
                <label class="flex items-center gap-md cursor-pointer">
                    <input type="checkbox" id="has_gas_meter" name="has_gas_meter" value="1" {{ old('has_gas_meter', $apartment->has_gas_meter) ? 'checked' : '' }}
                        class="w-5 h-5 rounded border-outline-variant text-primary focus:ring-primary-container" />
                    <span class="font-label-caps text-label-caps text-on-surface-variant">{{ __('messages.apartments.has_gas_meter') }}</span>
                </label>
                @error('has_gas_meter')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex items-center gap-md mt-xl pt-lg border-t border-outline-variant">
            <button type="submit" class="px-lg py-2 bg-primary text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all">
                <span class="material-symbols-outlined" data-icon="save">save</span>
                {{ __('messages.common.save') }}
            </button>
            <a href="{{ route('apartments.index') }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
                {{ __('messages.common.cancel') }}
            </a>
        </div>
    </form>
</div>
@endsection