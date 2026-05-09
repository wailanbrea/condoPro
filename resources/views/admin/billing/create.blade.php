@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <a href="{{ route('billing.index') }}" class="text-body-sm hover:text-primary transition-colors">{{ __('messages.billing.title') }}</a>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">Nueva Factura</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">Nueva Factura</h2>
        <p class="text-on-surface-variant">Crear una nueva factura mensual</p>
    </div>
    <a href="{{ route('billing.index') }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
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
    <form action="{{ route('billing.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="condominium_id">Condominio <span class="text-error">*</span></label>
                <select id="condominium_id" name="condominium_id" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('condominium_id') border-error ring-1 ring-error @enderror">
                    <option value="">Seleccionar condominio</option>
                    @foreach($condominiums as $id => $name)
                        <option value="{{ $id }}" {{ old('condominium_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @error('condominium_id')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="apartment_id">{{ __('messages.common.apartment') }} <span class="text-error">*</span></label>
                <select id="apartment_id" name="apartment_id" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('apartment_id') border-error ring-1 ring-error @enderror"
                    data-initial="{{ old('apartment_id') }}">
                    <option value="">— Seleccionar condominio primero —</option>
                </select>
                @error('apartment_id')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="billing_month">Mes de Facturación <span class="text-error">*</span></label>
                <select id="billing_month" name="billing_month" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('billing_month') border-error ring-1 ring-error @enderror">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ old('billing_month', date('n')) == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                    @endfor
                </select>
                @error('billing_month')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="billing_year">Año de Facturación <span class="text-error">*</span></label>
                <input type="number" id="billing_year" name="billing_year" value="{{ old('billing_year', date('Y')) }}" min="2020" max="2050" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('billing_year') border-error ring-1 ring-error @enderror" />
                @error('billing_year')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="due_date">{{ __('messages.billing.due_date') }} <span class="text-error">*</span></label>
                <input type="date" id="due_date" name="due_date" value="{{ old('due_date') }}" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('due_date') border-error ring-1 ring-error @enderror" />
                @error('due_date')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Apartment info preview --}}
            <div id="apartmentInfo" class="hidden">
                <div class="p-md bg-surface-container-low rounded-lg">
                    <span class="font-label-caps text-label-caps text-on-surface-variant block mb-xs">Información del Apartamento</span>
                    <div class="grid grid-cols-2 gap-sm">
                        <div>
                            <span class="text-body-sm text-on-surface-variant">Cuota Mantenimiento:</span>
                            <span id="aptInfoFee" class="font-mono-data text-on-surface font-bold block">RD$0.00</span>
                        </div>
                        <div>
                            <span class="text-body-sm text-on-surface-variant">Balance Actual:</span>
                            <span id="aptInfoBalance" class="font-mono-data text-on-surface font-bold block">RD$0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-lg p-md bg-surface-container-low rounded-lg flex items-start gap-md">
            <span class="material-symbols-outlined text-primary mt-0.5" data-icon="info">info</span>
            <p class="text-body-sm text-on-surface-variant">El subtotal, balance anterior, pagos aplicados y total se calcularán automáticamente al crear la factura.</p>
        </div>

        <div class="flex items-center gap-md mt-xl pt-lg border-t border-outline-variant">
            <button type="submit" class="px-lg py-2 bg-primary text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all">
                <span class="material-symbols-outlined" data-icon="save">save</span>
                {{ __('messages.common.save') }}
            </button>
            <a href="{{ route('billing.index') }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
                {{ __('messages.common.cancel') }}
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function() {
    const condoSelect = document.getElementById('condominium_id');
    const aptSelect = document.getElementById('apartment_id');
    const aptInfo = document.getElementById('apartmentInfo');
    const aptInfoFee = document.getElementById('aptInfoFee');
    const aptInfoBalance = document.getElementById('aptInfoBalance');
    let aptData = [];

    function loadApartments(condoId) {
        if (!condoId) {
            aptSelect.innerHTML = '<option value="">— Seleccionar condominio primero —</option>';
            aptInfo.classList.add('hidden');
            return;
        }

        aptSelect.innerHTML = '<option value="">Cargando...</option>';

        fetch('{{ url("/admin/api/apartments-by-condominium") }}/' + condoId, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            aptData = data;
            aptSelect.innerHTML = '<option value="">Seleccionar apartamento</option>';
            data.forEach(function(apt) {
                var opt = document.createElement('option');
                opt.value = apt.id;
                opt.textContent = apt.number;
                if (apt.id == aptSelect.dataset.initial) opt.selected = true;
                aptSelect.appendChild(opt);
            });
            if (aptSelect.dataset.initial) {
                showApartmentInfo(aptSelect.dataset.initial);
            }
        })
        .catch(function() {
            aptSelect.innerHTML = '<option value="">Error al cargar</option>';
        });
    }

    function showApartmentInfo(aptId) {
        if (!aptId) {
            aptInfo.classList.add('hidden');
            return;
        }
        var apt = aptData.find(function(a) { return a.id == aptId; });
        if (!apt) { aptInfo.classList.add('hidden'); return; }
        aptInfoFee.textContent = 'RD$' + parseFloat(apt.maintenance_fee || 0).toFixed(2);
        aptInfoBalance.textContent = 'RD$' + parseFloat(apt.area || 0).toFixed(2);
        aptInfo.classList.remove('hidden');
    }

    condoSelect.addEventListener('change', function() { loadApartments(this.value); });
    aptSelect.addEventListener('change', function() { showApartmentInfo(this.value); });

    var initialCondo = condoSelect.value;
    if (initialCondo) loadApartments(initialCondo);
})();
</script>
@endpush
@endsection