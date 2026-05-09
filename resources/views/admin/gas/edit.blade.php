@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <a href="{{ route('gas.index') }}" class="text-body-sm hover:text-primary transition-colors">{{ __('messages.nav.gas') }}</a>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">Editar Lectura</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">Editar Lectura de Gas</h2>
        <p class="text-on-surface-variant">{{ $gas->apartment->number ?? '—' }} — {{ $gas->condominium->name ?? '—' }}</p>
    </div>
    <a href="{{ route('gas.index') }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
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

@if($gas->billed)
    <div class="mb-lg px-lg py-md bg-amber-100 text-amber-800 rounded-lg flex items-center gap-2">
        <span class="material-symbols-outlined" data-icon="lock">lock</span>
        Esta lectura ya fue facturada y no puede ser modificada.
    </div>
@else

<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg max-w-4xl">
    <form action="{{ route('gas.update', $gas) }}" method="POST" id="gasEditForm">
        @csrf
        @method('PUT')

        {{-- Configuration Section --}}
        <div class="border-l-4 border-primary pl-md mb-xl">
            <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined" data-icon="settings">settings</span>
                Configuración
            </h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-lg mb-xl">
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="condominium_id">Condominio <span class="text-error">*</span></label>
                <select id="condominium_id" name="condominium_id" required class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface">
                    @foreach($condominiums as $id => $name)
                        <option value="{{ $id }}" {{ old('condominium_id', $gas->condominium_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="apartment_id">{{ __('messages.common.apartment') }} <span class="text-error">*</span></label>
                <select id="apartment_id" name="apartment_id" required class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface">
                    @foreach($apartments as $id => $number)
                        <option value="{{ $id }}" {{ old('apartment_id', $gas->apartment_id) == $id ? 'selected' : '' }}>{{ $number }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="billing_month">Mes Facturación <span class="text-error">*</span></label>
                <select id="billing_month" name="billing_month" required class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ old('billing_month', $gas->billing_month) == $m ? 'selected' : '' }}>{{ ucfirst(\Carbon\Carbon::create()->month($m)->monthName) }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="billing_year">Año <span class="text-error">*</span></label>
                <input type="number" id="billing_year" name="billing_year" value="{{ old('billing_year', $gas->billing_year) }}" min="2020" max="2040" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface" />
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="meter_number">Número de Contador</label>
                <input type="text" id="meter_number" name="meter_number" value="{{ old('meter_number', $gas->meter_number) }}"
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface" />
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="reading_date_start">Fecha Lectura Inicial <span class="text-error">*</span></label>
                <input type="date" id="reading_date_start" name="reading_date_start" value="{{ old('reading_date_start', $gas->reading_date_start?->format('Y-m-d')) }}" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface" />
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="reading_date_end">Fecha Lectura Final <span class="text-error">*</span></label>
                <input type="date" id="reading_date_end" name="reading_date_end" value="{{ old('reading_date_end', $gas->reading_date_end?->format('Y-m-d')) }}" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface" />
            </div>
        </div>

        <div class="border-t border-outline-variant my-xl"></div>

        {{-- Readings Section --}}
        <div class="border-l-4 border-secondary pl-md mb-lg">
            <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined" data-icon="speed">speed</span>
                Lecturas del Contador
            </h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-lg mb-xl">
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="reading_initial">Lectura Anterior (m³) <span class="text-error">*</span></label>
                <input type="number" id="reading_initial" name="reading_initial" value="{{ old('reading_initial', $gas->reading_initial) }}" step="0.001" min="0" required
                    class="w-full px-md py-3 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface text-right font-mono-data text-lg" />
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="reading_final">Lectura Actual (m³) <span class="text-error">*</span></label>
                <input type="number" id="reading_final" name="reading_final" value="{{ old('reading_final', $gas->reading_final) }}" step="0.001" min="0" required
                    class="w-full px-md py-3 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface text-right font-mono-data text-lg" />
            </div>
        </div>

        <div id="readingError" class="hidden mb-lg p-md bg-[#FFEBE6] text-[#BF2600] rounded-lg flex items-center gap-2">
            <span class="material-symbols-outlined" data-icon="warning">warning</span>
            <span id="readingErrorMsg" class="text-body-sm"></span>
        </div>

        {{-- Pricing Section --}}
        <div class="border-l-4 border-tertiary pl-md mb-lg">
            <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined" data-icon="payments">payments</span>
                Tarifas
            </h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-lg mb-xl">
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="conversion_factor">Factor de Conversión <span class="text-error">*</span></label>
                <input type="number" id="conversion_factor" name="conversion_factor" value="{{ old('conversion_factor', $gas->conversion_factor) }}" step="0.0001" min="0" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface text-right font-mono-data" />
            </div>
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="gallon_price">Precio por Galón (RD$) <span class="text-error">*</span></label>
                <input type="number" id="gallon_price" name="gallon_price" value="{{ old('gallon_price', $gas->gallon_price) }}" step="0.01" min="0" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface text-right font-mono-data" />
            </div>
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="extra_cost_per_gallon">Costo Adicional/Galón (RD$)</label>
                <input type="number" id="extra_cost_per_gallon" name="extra_cost_per_gallon" value="{{ old('extra_cost_per_gallon', $gas->extra_cost_per_gallon) }}" step="0.01" min="0"
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface text-right font-mono-data" />
            </div>
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1">Precio Total por Galón (RD$)</label>
                <div id="totalGallonPriceDisplay" class="w-full px-md py-2 border border-outline rounded-lg bg-surface-container text-on-surface text-right font-mono-data font-bold">
                    {{ number_format($gas->total_gallon_price, 2) }}
                </div>
            </div>
        </div>

        <input type="hidden" name="price_per_gallon" id="price_per_gallon_hidden" value="{{ old('gallon_price', $gas->gallon_price) }}" />

        {{-- Calculation Preview --}}
        <div id="gasCalcPreview" class="bg-surface-container-low rounded-xl p-lg mb-xl">
            <h4 class="font-label-caps text-label-caps text-on-surface-variant mb-md">Resumen del Cálculo</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-primary text-white">
                            <th class="px-md py-2 text-label-caps font-label-caps whitespace-nowrap">Cálculo</th>
                            <th class="px-md py-2 text-label-caps font-label-caps whitespace-nowrap text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-outline-variant"><td class="px-md py-2 text-body-sm">Lectura Actual</td><td class="px-md py-2 text-right font-mono-data" id="calcFinal">{{ number_format($gas->reading_final, 3) }}</td></tr>
                        <tr class="border-b border-outline-variant"><td class="px-md py-2 text-body-sm">Lectura Anterior</td><td class="px-md py-2 text-right font-mono-data" id="calcInitial">{{ number_format($gas->reading_initial, 3) }}</td></tr>
                        <tr class="border-b border-outline-variant bg-surface-container-lowest"><td class="px-md py-2 text-body-sm font-bold">Consumo (m³)</td><td class="px-md py-2 text-right font-mono-data font-bold" id="calcConsumption">{{ number_format($gas->consumption_m3, 3) }}</td></tr>
                        <tr class="border-b border-outline-variant"><td class="px-md py-2 text-body-sm">× Factor de Conversión</td><td class="px-md py-2 text-right font-mono-data" id="calcFactor">{{ number_format($gas->conversion_factor, 4) }}</td></tr>
                        <tr class="border-b border-outline-variant bg-surface-container-lowest"><td class="px-md py-2 text-body-sm font-bold">Galones</td><td class="px-md py-2 text-right font-mono-data font-bold" id="calcGallons">{{ number_format($gas->gallons, 2) }}</td></tr>
                        <tr class="border-b border-outline-variant"><td class="px-md py-2 text-body-sm">× Precio Total por Galón</td><td class="px-md py-2 text-right font-mono-data" id="calcPrice">RD${{ number_format($gas->total_gallon_price, 2) }}</td></tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-primary-container border-t-2 border-primary">
                            <td class="px-md py-md font-bold text-label-caps text-primary">TOTAL A FACTURAR</td>
                            <td class="px-md py-md font-mono-data text-headline-lg text-primary font-bold text-right" id="calcTotal">RD${{ number_format($gas->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="flex items-center gap-md pt-lg border-t border-outline-variant">
            <button type="submit" class="px-lg py-2 bg-primary text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all">
                <span class="material-symbols-outlined" data-icon="save">save</span>
                {{ __('messages.common.save') }}
            </button>
            <a href="{{ route('gas.index') }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
                {{ __('messages.common.cancel') }}
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function() {
    const readingInitial = document.getElementById('reading_initial');
    const readingFinal = document.getElementById('reading_final');
    const conversionFactor = document.getElementById('conversion_factor');
    const gallonPrice = document.getElementById('gallon_price');
    const extraCostPerGallon = document.getElementById('extra_cost_per_gallon');
    const totalGallonPriceDisplay = document.getElementById('totalGallonPriceDisplay');
    const pricePerGallonHidden = document.getElementById('price_per_gallon_hidden');
    const errorDiv = document.getElementById('readingError');
    const errorMsg = document.getElementById('readingErrorMsg');

    const calcInitial = document.getElementById('calcInitial');
    const calcFinal = document.getElementById('calcFinal');
    const calcConsumption = document.getElementById('calcConsumption');
    const calcFactor = document.getElementById('calcFactor');
    const calcGallons = document.getElementById('calcGallons');
    const calcPrice = document.getElementById('calcPrice');
    const calcTotal = document.getElementById('calcTotal');

    function calculate() {
        const initial = parseFloat(readingInitial.value) || 0;
        const final_ = parseFloat(readingFinal.value) || 0;
        const factor = parseFloat(conversionFactor.value) || 0;
        const price = parseFloat(gallonPrice.value) || 0;
        const extra = parseFloat(extraCostPerGallon.value) || 0;
        const totalGallonPrice = price + extra;

        const consumption = final_ - initial;
        const gallons = consumption * factor;
        const total = gallons * totalGallonPrice;

        if (final_ > 0 && initial_ > 0 && final_ < initial) {
            errorDiv.classList.remove('hidden');
            errorMsg.textContent = 'La lectura actual no puede ser menor que la lectura anterior.';
            readingFinal.classList.add('border-error');
        } else {
            errorDiv.classList.add('hidden');
            readingFinal.classList.remove('border-error');
        }

        calcInitial.textContent = initial.toFixed(3);
        calcFinal.textContent = final_.toFixed(3);
        calcConsumption.textContent = Math.max(0, consumption).toFixed(3);
        calcFactor.textContent = factor.toFixed(4);
        calcGallons.textContent = Math.max(0, gallons).toFixed(2);
        calcPrice.textContent = 'RD$' + totalGallonPrice.toFixed(2);
        calcTotal.textContent = 'RD$' + Math.max(0, total).toFixed(2);
        totalGallonPriceDisplay.textContent = 'RD$' + totalGallonPrice.toFixed(2);
        pricePerGallonHidden.value = price;
    }

    [readingInitial, readingFinal, conversionFactor, gallonPrice, extraCostPerGallon].forEach(function(el) {
        el.addEventListener('input', calculate);
    });

    calculate();
})();
</script>
@endpush
@endif
@endsection