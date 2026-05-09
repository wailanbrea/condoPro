@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <a href="{{ route('extra-charges.index') }}" class="text-body-sm hover:text-primary transition-colors">{{ __('messages.nav.extra_fees') }}</a>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">Editar Cuota</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">Editar Cuota Extraordinaria</h2>
        <p class="text-on-surface-variant">{{ $extraCharge->title }}</p>
    </div>
    <a href="{{ route('extra-charges.index') }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
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
    <form action="{{ route('extra-charges.update', $extraCharge) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1">Condominio</label>
                <input type="text" disabled value="{{ $extraCharge->condominium->name }}"
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface-variant cursor-not-allowed" />
            </div>

            <div class="md:col-span-2">
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="title">Título <span class="text-error">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title', $extraCharge->title) }}" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('title') border-error ring-1 ring-error @enderror" />
                @error('title')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="description">Descripción</label>
                <textarea id="description" name="description" rows="3"
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('description') border-error ring-1 ring-error @enderror">{{ old('description', $extraCharge->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="total_amount">Monto Total <span class="text-error">*</span></label>
                <div class="relative">
                    <span class="absolute left-md top-1/2 -translate-y-1/2 font-mono-data text-on-surface-variant">RD$</span>
                    <input type="number" id="total_amount" name="total_amount" value="{{ old('total_amount', $extraCharge->total_amount) }}" step="0.01" min="0" required
                        class="w-full pl-xl pr-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface font-mono-data focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('total_amount') border-error ring-1 ring-error @enderror" />
                </div>
                @error('total_amount')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="distribution_type">Tipo de Reparto <span class="text-error">*</span></label>
                <select id="distribution_type" name="distribution_type" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('distribution_type') border-error ring-1 ring-error @enderror">
                    <option value="equal" {{ old('distribution_type', $extraCharge->distribution_type) === 'equal' ? 'selected' : '' }}>Igualitario</option>
                    <option value="by_area" {{ old('distribution_type', $extraCharge->distribution_type) === 'by_area' ? 'selected' : '' }}>Por Área</option>
                    <option value="custom" {{ old('distribution_type', $extraCharge->distribution_type) === 'custom' ? 'selected' : '' }}>Manual</option>
                </select>
                @error('distribution_type')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
                <p id="distributionHelp" class="mt-1 text-body-sm text-on-surface-variant"></p>
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="start_month">Mes de Inicio</label>
                <select id="start_month" name="start_month"
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('start_month') border-error ring-1 ring-error @enderror">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ old('start_month', $extraCharge->start_month) == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                    @endfor
                </select>
                @error('start_month')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="start_year">Año de Inicio</label>
                <input type="number" id="start_year" name="start_year" value="{{ old('start_year', $extraCharge->start_year) }}" min="2020" max="2050"
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('start_year') border-error ring-1 ring-error @enderror" />
                @error('start_year')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="installments_count">Número de Cuotas</label>
                <input type="number" id="installments_count" name="installments_count" value="{{ old('installments_count', $extraCharge->installments_count) }}" min="1" max="60"
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('installments_count') border-error ring-1 ring-error @enderror" />
                @error('installments_count')
                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="status">{{ __('messages.common.status') }}</label>
                <select id="status" name="status"
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all @error('status') border-error ring-1 ring-error @enderror">
                    <option value="active" {{ old('status', $extraCharge->status ?? 'active') === 'active' ? 'selected' : '' }}>{{ __('messages.common.active') }}</option>
                    <option value="inactive" {{ old('status', $extraCharge->status ?? 'active') === 'inactive' ? 'selected' : '' }}>{{ __('messages.common.inactive') }}</option>
                </select>
            </div>
        </div>

        {{-- Distribution Preview --}}
        <div id="distributionPreview" class="mt-lg p-md bg-surface-container-low rounded-lg hidden">
            <h4 class="font-label-caps text-label-caps text-on-surface-variant mb-sm">Vista Previa de Distribución</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-white/50">
                        <tr>
                            <th class="px-md py-sm text-label-caps font-label-caps text-on-surface-variant">Apartamento</th>
                            <th class="px-md py-sm text-label-caps font-label-caps text-on-surface-variant">Área</th>
                            <th class="px-md py-sm text-label-caps font-label-caps text-on-surface-variant">Asignado</th>
                            <th class="px-md py-sm text-label-caps font-label-caps text-on-surface-variant">Cuota Mensual</th>
                        </tr>
                    </thead>
                    <tbody id="distributionTableBody" class="divide-y divide-surface-container-low">
                    </tbody>
                    <tfoot>
                        <tr class="bg-primary/5 font-bold">
                            <td class="px-md py-sm">Total</td>
                            <td class="px-md py-sm" id="totalArea">—</td>
                            <td class="px-md py-sm font-mono-data" id="totalAssigned">RD$0.00</td>
                            <td class="px-md py-sm font-mono-data" id="totalMonthly">RD$0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="flex items-center gap-md mt-xl pt-lg border-t border-outline-variant">
            <button type="submit" class="px-lg py-2 bg-primary text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all">
                <span class="material-symbols-outlined" data-icon="save">save</span>
                {{ __('messages.common.save') }}
            </button>
            <a href="{{ route('extra-charges.index') }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
                {{ __('messages.common.cancel') }}
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function() {
    var distributionType = document.getElementById('distribution_type');
    var totalAmount = document.getElementById('total_amount');
    var installmentsCount = document.getElementById('installments_count');
    var distributionHelp = document.getElementById('distributionHelp');
    var preview = document.getElementById('distributionPreview');
    var tbody = document.getElementById('distributionTableBody');
    var totalAreaEl = document.getElementById('totalArea');
    var totalAssignedEl = document.getElementById('totalAssigned');
    var totalMonthlyEl = document.getElementById('totalMonthly');

    var aptData = {{ $extraCharge->condominium->apartments->where('status', 'active')->map(fn($a) => ['id' => $a->id, 'number' => $a->number, 'area' => (float) $a->area, 'maintenance_fee' => (float) $a->maintenance_fee])->toJson() }};
    var selectedAptIds = {{ $extraCharge->extraChargeApartments->pluck('apartment_id')->toJson() }};

    var helpTexts = {
        equal: 'Se repartirá equitativamente entre todos los apartamentos.',
        by_area: 'Se repartirá proporcionalmente según el área de cada apartamento.',
        custom: 'Se creará sin montos asignados. Podrá editar los montos manualmente.'
    };

    function renderPreview() {
        var distType = distributionType.value;
        var amount = parseFloat(totalAmount.value) || 0;
        var installments = parseInt(installmentsCount.value) || 1;

        if (aptData.length === 0 || amount === 0) {
            preview.classList.add('hidden');
            return;
        }

        preview.classList.remove('hidden');

        var totalArea = 0;
        aptData.forEach(function(a) { totalArea += a.area; });

        var bodyHtml = '';
        var sumAssigned = 0;
        var sumMonthly = 0;

        aptData.forEach(function(apt) {
            var assigned = 0;
            var area = apt.area;

            if (distType === 'equal') {
                assigned = amount / aptData.length;
            } else if (distType === 'by_area') {
                var pct = totalArea > 0 ? (area / totalArea) * 100 : 0;
                assigned = amount * (pct / 100);
            }

            assigned = Math.round(assigned * 100) / 100;
            var monthly = Math.round((assigned / installments) * 100) / 100;
            sumAssigned += assigned;
            sumMonthly += monthly;

            bodyHtml += '<tr class="hover:bg-surface-container-low transition-colors">';
            bodyHtml += '<td class="px-md py-sm text-on-surface">' + apt.number + '</td>';
            bodyHtml += '<td class="px-md py-sm text-on-surface-variant">' + area.toFixed(1) + ' m²</td>';
            bodyHtml += '<td class="px-md py-sm font-mono-data">RD$' + assigned.toFixed(2) + '</td>';
            bodyHtml += '<td class="px-md py-sm font-mono-data">' + monthly.toFixed(2) + '</td>';
            bodyHtml += '</tr>';
        });

        tbody.innerHTML = bodyHtml;
        totalAreaEl.textContent = totalArea.toFixed(1) + ' m²';
        totalAssignedEl.textContent = 'RD$' + sumAssigned.toFixed(2);
        totalMonthlyEl.textContent = 'RD$' + sumMonthly.toFixed(2);
    }

    distributionType.addEventListener('change', function() {
        distributionHelp.textContent = helpTexts[this.value] || '';
        renderPreview();
    });

    totalAmount.addEventListener('input', renderPreview);
    installmentsCount.addEventListener('input', renderPreview);

    distributionHelp.textContent = helpTexts[distributionType.value] || '';
    renderPreview();
})();
</script>
@endpush
@endsection