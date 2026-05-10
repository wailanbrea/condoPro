@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <a href="{{ route('extra-charges.index') }}" class="text-body-sm hover:text-primary transition-colors">{{ app()->getLocale() === 'es' ? 'Imprevistos' : 'Extra Charges' }}</a>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ app()->getLocale() === 'es' ? 'Nuevo Imprevisto' : 'New Extra Charge' }}</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">{{ app()->getLocale() === 'es' ? 'Nuevo Imprevisto' : 'New Extra Charge' }}</h2>
        <p class="text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Gasto imprevisto a repartir entre los apartamentos' : 'Unforeseen expense to distribute among apartments' }}</p>
    </div>
    <a href="{{ route('extra-charges.index') }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
        <span class="material-symbols-outlined" data-icon="arrow_back">arrow_back</span>
        {{ __('messages.common.back_to_list') }}
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-lg">
    {{-- Form --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-lg border border-outline-variant/30">
        <form action="{{ route('extra-charges.store') }}" method="POST" id="extraChargeForm">
            @csrf
            <div class="space-y-lg">
                <div>
                    <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="condominium_id">{{ app()->getLocale() === 'es' ? 'Condominio' : 'Condominium' }} <span class="text-error">*</span></label>
                    <select id="condominium_id" name="condominium_id" required
                        class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container">
                        <option value="">{{ app()->getLocale() === 'es' ? 'Seleccionar condominio' : 'Select condominium' }}</option>
                        @foreach($condominiums as $id => $name)
                            <option value="{{ $id }}" {{ old('condominium_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="title">{{ app()->getLocale() === 'es' ? 'Título del Imprevisto' : 'Extra Charge Title' }} <span class="text-error">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" required
                        class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container"
                        placeholder="{{ app()->getLocale() === 'es' ? 'Ej: Reparación de bomba, Pintura del edificio...' : 'E.g.: Pump repair, Building painting...' }}" />
                </div>

                <div>
                    <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="description">{{ app()->getLocale() === 'es' ? 'Descripción' : 'Description' }}</label>
                    <textarea id="description" name="description" rows="2"
                        class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-lg">
                    <div>
                        <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="total_amount">{{ app()->getLocale() === 'es' ? 'Monto Total' : 'Total Amount' }} <span class="text-error">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 font-mono-data text-on-surface-variant">RD$</span>
                            <input type="number" id="total_amount" name="total_amount" value="{{ old('total_amount') }}" step="0.01" min="0" required
                                class="w-full pl-12 pr-3 py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface font-mono-data text-lg focus:ring-2 focus:ring-primary-container"
                                onchange="updatePreview()" onkeyup="updatePreview()" />
                        </div>
                    </div>
                    <div>
                        <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="installments_count">{{ app()->getLocale() === 'es' ? 'Número de Cuotas' : 'Installments' }} <span class="text-error">*</span></label>
                        <input type="number" id="installments_count" name="installments_count" value="{{ old('installments_count', 1) }}" min="1" max="60"
                            class="w-full px-3 py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface text-center text-lg focus:ring-2 focus:ring-primary-container"
                            onchange="updatePreview()" onkeyup="updatePreview()" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-lg">
                    <div>
                        <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="start_month">{{ app()->getLocale() === 'es' ? 'Mes de Inicio' : 'Start Month' }}</label>
                        <select id="start_month" name="start_month"
                            class="w-full px-3 py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ old('start_month', date('n')) == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="start_year">{{ app()->getLocale() === 'es' ? 'Año de Inicio' : 'Start Year' }}</label>
                        <input type="number" id="start_year" name="start_year" value="{{ old('start_year', date('Y')) }}" min="2020" max="2050"
                            class="w-full px-3 py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface text-center focus:ring-2 focus:ring-primary-container" />
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1">{{ app()->getLocale() === 'es' ? 'Apartamentos' : 'Apartments' }}</label>
                    <div id="apartmentsContainer" class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto p-3 border border-outline-variant rounded-lg bg-surface-container-lowest">
                        <p class="col-span-3 text-body-sm text-on-surface-variant text-center py-4">{{ app()->getLocale() === 'es' ? 'Seleccione un condominio primero' : 'Select a condominium first' }}</p>
                    </div>
                </div>

                <input type="hidden" name="distribution_type" value="equal" />

                <div class="flex items-center gap-3 pt-2 border-t border-outline-variant/30">
                    <button type="submit" class="px-xl py-3 bg-primary text-white rounded-lg flex items-center gap-2 font-bold hover:brightness-110 transition-all">
                        <span class="material-symbols-outlined">save</span>
                        {{ app()->getLocale() === 'es' ? 'Crear Imprevisto' : 'Create Extra Charge' }}
                    </button>
                    <a href="{{ route('extra-charges.index') }}" class="px-lg py-3 bg-white border border-outline-variant rounded-lg hover:bg-surface-container-low transition-colors">
                        {{ __('messages.common.cancel') }}
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Calculation Preview Card --}}
    <div class="space-y-lg">
        <div id="calcPreview" class="bg-white rounded-xl shadow-sm p-lg border border-outline-variant/30 sticky top-24">
            <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-sm mb-lg">
                <span class="material-symbols-outlined text-primary">calculate</span>
                {{ app()->getLocale() === 'es' ? 'Resumen del Cálculo' : 'Calculation Summary' }}
            </h3>

            <div class="space-y-md">
                <div class="bg-surface-container-low rounded-lg p-md">
                    <p class="text-label-caps text-label-caps text-on-surface-variant mb-sm">{{ app()->getLocale() === 'es' ? 'Monto Total' : 'Total Amount' }}</p>
                    <p class="font-mono-data text-headline-lg text-on-surface font-bold" id="previewTotal">RD$0.00</p>
                </div>

                <div class="flex items-center justify-center text-on-surface-variant">
                    <span class="material-symbols-outlined">arrow_downward</span>
                </div>

                <div class="grid grid-cols-2 gap-sm">
                    <div class="bg-surface-container-low rounded-lg p-md text-center">
                        <p class="text-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Apartamentos' : 'Apartments' }}</p>
                        <p class="font-mono-data text-headline-md text-primary font-bold" id="previewApts">0</p>
                    </div>
                    <div class="bg-surface-container-low rounded-lg p-md text-center">
                        <p class="text-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Cuotas' : 'Installments' }}</p>
                        <p class="font-mono-data text-headline-md text-primary font-bold" id="previewQuotas">1</p>
                    </div>
                </div>

                <div class="flex items-center justify-center text-on-surface-variant">
                    <span class="material-symbols-outlined">arrow_downward</span>
                </div>

                <div class="bg-primary/10 rounded-lg p-md border border-primary/20">
                    <p class="text-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Cada apartamento paga por mes' : 'Each apartment pays per month' }}</p>
                    <p class="font-mono-data text-headline-xl text-primary font-bold" id="previewPerMonth">RD$0.00</p>
                </div>

                <div class="text-body-sm text-on-surface-variant" id="previewDetail">
                    {{ app()->getLocale() === 'es' ? 'Ingrese el monto total, número de cuotas y seleccione apartamentos' : 'Enter total amount, installments and select apartments' }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
var allApartments = [];

document.getElementById('condominium_id').addEventListener('change', function() {
    var condoId = this.value;
    var container = document.getElementById('apartmentsContainer');
    container.innerHTML = '<p class="col-span-3 text-body-sm text-on-surface-variant text-center py-4">Cargando apartamentos...</p>';
    
    var url = condoId ? '/admin' : '';
    url = '/admin/api/apartments-by-condominium/' + condoId;
    
    fetch(url)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            allApartments = data;
            container.innerHTML = '';
            data.forEach(function(apt) {
                var label = document.createElement('label');
                label.className = 'flex items-center gap-2 p-2 rounded-lg hover:bg-surface-container-low cursor-pointer border border-outline-variant/30';
                label.innerHTML = 
                    '<input type="checkbox" name="apartment_ids[]" value="' + apt.id + '" checked ' +
                    'onchange="updatePreview()" class="w-4 h-4 rounded border-outline-variant text-primary" />' +
                    '<span class="text-body-md text-on-surface">' + apt.number + '</span>';
                container.appendChild(label);
            });
            if (data.length === 0) {
                container.innerHTML = '<p class="col-span-3 text-body-sm text-on-surface-variant text-center py-4">No hay apartamentos activos</p>';
            }
            updatePreview();
        })
        .catch(function() {
            container.innerHTML = '<p class="col-span-3 text-body-sm text-error text-center py-4">Error al cargar apartamentos</p>';
        });
});

function updatePreview() {
    var total = parseFloat(document.getElementById('total_amount').value) || 0;
    var quotas = parseInt(document.getElementById('installments_count').value) || 1;
    var selected = document.querySelectorAll('#apartmentsContainer input:checked');
    var aptCount = selected.length;

    document.getElementById('previewTotal').textContent = 'RD$' + number(total, 2);
    document.getElementById('previewApts').textContent = aptCount;
    document.getElementById('previewQuotas').textContent = quotas;

    if (aptCount > 0 && total > 0) {
        var perApt = total / aptCount;
        var perMonth = perApt / quotas;
        document.getElementById('previewPerMonth').textContent = 'RD$' + number(perMonth, 2);
        document.getElementById('previewDetail').innerHTML = 
            '<strong>RD$' + number(total, 2) + '</strong> ÷ ' + aptCount + ' {{ app()->getLocale() === 'es' ? 'aptos' : 'apts' }} = ' +
            '<strong>RD$' + number(perApt, 2) + '</strong> c/u ÷ ' + quotas + ' {{ app()->getLocale() === 'es' ? 'cuotas' : 'installments' }} = ' +
            '<strong class="text-primary">RD$' + number(perMonth, 2) + '</strong> por mes';
    } else {
        document.getElementById('previewPerMonth').textContent = 'RD$0.00';
        document.getElementById('previewDetail').textContent = '{{ app()->getLocale() === 'es' ? 'Ingrese el monto total y seleccione apartamentos' : 'Enter total amount and select apartments' }}';
    }
}

function number(val, dec) {
    return val.toFixed(dec).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}
</script>
@endpush
@endsection