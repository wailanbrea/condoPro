@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ __('messages.nav.reports') }}</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">Reportes</h2>
        <p class="text-on-surface-variant">Generación de reportes y estadísticas</p>
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

<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg mb-xl">
    <h3 class="font-headline-md text-headline-md text-on-surface mb-md">Filtros</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-lg">
        <div>
            <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="filter_condo">Condominio</label>
            <select id="filter_condo" class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all">
                @foreach($condominiums as $condo)
                    <option value="{{ $condo->id }}" {{ $condo->id == auth()->user()->condominium_id ? 'selected' : '' }}>{{ $condo->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="filter_month">Mes</label>
            <select id="filter_month" class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="filter_year">Año</label>
            <select id="filter_year" class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all">
                @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter">
    <a href="#" class="report-link bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg hover:shadow-[0px_4px_12px_rgba(0,0,0,0.1)] transition-all group" data-report="apartment-statement">
        <div class="flex items-center gap-md mb-md">
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                <span class="material-symbols-outlined text-primary text-2xl" data-icon="receipt_long">receipt_long</span>
            </div>
            <div>
                <h3 class="font-headline-md text-headline-md text-on-surface">Estado de Cuenta</h3>
                <p class="text-body-sm text-on-surface-variant">Por apartamento</p>
            </div>
        </div>
        <p class="text-body-sm text-on-surface-variant mb-lg">Consulta el estado de cuenta detallado de cada apartamento, incluyendo saldos y pagos.</p>
        <div class="flex items-center gap-2 text-primary font-label-caps text-label-caps group-hover:gap-3 transition-all">
            <span>Ver Reporte</span>
            <span class="material-symbols-outlined text-lg" data-icon="arrow_forward">arrow_forward</span>
        </div>
    </a>

    <a href="#" class="report-link bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg hover:shadow-[0px_4px_12px_rgba(0,0,0,0.1)] transition-all group" data-report="monthly-income">
        <div class="flex items-center gap-md mb-md">
            <div class="w-12 h-12 rounded-xl bg-secondary/10 flex items-center justify-center group-hover:bg-secondary/20 transition-colors">
                <span class="material-symbols-outlined text-secondary text-2xl" data-icon="trending_up">trending_up</span>
            </div>
            <div>
                <h3 class="font-headline-md text-headline-md text-on-surface">Ingresos por Mes</h3>
                <p class="text-body-sm text-on-surface-variant">Resumen mensual</p>
            </div>
        </div>
        <p class="text-body-sm text-on-surface-variant mb-lg">Analiza los ingresos del condominio organizados por mes.</p>
        <div class="flex items-center gap-2 text-secondary font-label-caps text-label-caps group-hover:gap-3 transition-all">
            <span>Ver Reporte</span>
            <span class="material-symbols-outlined text-lg" data-icon="arrow_forward">arrow_forward</span>
        </div>
    </a>

    <a href="#" class="report-link bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg hover:shadow-[0px_4px_12px_rgba(0,0,0,0.1)] transition-all group" data-report="monthly-expenses">
        <div class="flex items-center gap-md mb-md">
            <div class="w-12 h-12 rounded-xl bg-error/10 flex items-center justify-center group-hover:bg-error/20 transition-colors">
                <span class="material-symbols-outlined text-error text-2xl" data-icon="account_balance_wallet">account_balance_wallet</span>
            </div>
            <div>
                <h3 class="font-headline-md text-headline-md text-on-surface">Egresos por Mes</h3>
                <p class="text-body-sm text-on-surface-variant">Desglose de gastos</p>
            </div>
        </div>
        <p class="text-body-sm text-on-surface-variant mb-lg">Consulta los egresos organizados por mes y categoría.</p>
        <div class="flex items-center gap-2 text-error font-label-caps text-label-caps group-hover:gap-3 transition-all">
            <span>Ver Reporte</span>
            <span class="material-symbols-outlined text-lg" data-icon="arrow_forward">arrow_forward</span>
        </div>
    </a>

    <a href="#" class="report-link bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg hover:shadow-[0px_4px_12px_rgba(0,0,0,0.1)] transition-all group" data-report="monthly-balance">
        <div class="flex items-center gap-md mb-md">
            <div class="w-12 h-12 rounded-xl bg-tertiary/10 flex items-center justify-center group-hover:bg-tertiary/20 transition-colors">
                <span class="material-symbols-outlined text-tertiary text-2xl" data-icon="balance">balance</span>
            </div>
            <div>
                <h3 class="font-headline-md text-headline-md text-on-surface">Balance Mensual</h3>
                <p class="text-body-sm text-on-surface-variant">Ingresos vs Egresos</p>
            </div>
        </div>
        <p class="text-body-sm text-on-surface-variant mb-lg">Compara ingresos y egresos mensuales.</p>
        <div class="flex items-center gap-2 text-tertiary font-label-caps text-label-caps group-hover:gap-3 transition-all">
            <span>Ver Reporte</span>
            <span class="material-symbols-outlined text-lg" data-icon="arrow_forward">arrow_forward</span>
        </div>
    </a>

    <a href="#" class="report-link bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg hover:shadow-[0px_4px_12px_rgba(0,0,0,0.1)] transition-all group" data-report="debtors">
        <div class="flex items-center gap-md mb-md">
            <div class="w-12 h-12 rounded-xl bg-error/10 flex items-center justify-center group-hover:bg-error/20 transition-colors">
                <span class="material-symbols-outlined text-error text-2xl" data-icon="person_off">person_off</span>
            </div>
            <div>
                <h3 class="font-headline-md text-headline-md text-on-surface">Deudores</h3>
                <p class="text-body-sm text-on-surface-variant">Apartamentos con deuda</p>
            </div>
        </div>
        <p class="text-body-sm text-on-surface-variant mb-lg">Lista de apartamentos con saldos pendientes.</p>
        <div class="flex items-center gap-2 text-error font-label-caps text-label-caps group-hover:gap-3 transition-all">
            <span>Ver Reporte</span>
            <span class="material-symbols-outlined text-lg" data-icon="arrow_forward">arrow_forward</span>
        </div>
    </a>

    <a href="#" class="report-link bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg hover:shadow-[0px_4px_12px_rgba(0,0,0,0.1)] transition-all group" data-report="pending-payments">
        <div class="flex items-center gap-md mb-md">
            <div class="w-12 h-12 rounded-xl bg-tertiary-fixed/30 flex items-center justify-center group-hover:bg-tertiary-fixed/50 transition-colors">
                <span class="material-symbols-outlined text-tertiary text-2xl" data-icon="schedule">schedule</span>
            </div>
            <div>
                <h3 class="font-headline-md text-headline-md text-on-surface">Pagos Pendientes</h3>
                <p class="text-body-sm text-on-surface-variant">Por confirmar</p>
            </div>
        </div>
        <p class="text-body-sm text-on-surface-variant mb-lg">Pagos registrados que aún no han sido confirmados.</p>
        <div class="flex items-center gap-2 text-tertiary font-label-caps text-label-caps group-hover:gap-3 transition-all">
            <span>Ver Reporte</span>
            <span class="material-symbols-outlined text-lg" data-icon="arrow_forward">arrow_forward</span>
        </div>
    </a>

    <a href="#" class="report-link bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg hover:shadow-[0px_4px_12px_rgba(0,0,0,0.1)] transition-all group" data-report="gas-consumption">
        <div class="flex items-center gap-md mb-md">
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                <span class="material-symbols-outlined text-primary text-2xl" data-icon="local_gas_station">local_gas_station</span>
            </div>
            <div>
                <h3 class="font-headline-md text-headline-md text-on-surface">Consumo de Gas</h3>
                <p class="text-body-sm text-on-surface-variant">Lecturas y consumo</p>
            </div>
        </div>
        <p class="text-body-sm text-on-surface-variant mb-lg">Reporte de consumo de gas por apartamento.</p>
        <div class="flex items-center gap-2 text-primary font-label-caps text-label-caps group-hover:gap-3 transition-all">
            <span>Ver Reporte</span>
            <span class="material-symbols-outlined text-lg" data-icon="arrow_forward">arrow_forward</span>
        </div>
    </a>

    <a href="#" class="report-link bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg hover:shadow-[0px_4px_12px_rgba(0,0,0,0.1)] transition-all group" data-report="extra-charges">
        <div class="flex items-center gap-md mb-md">
            <div class="w-12 h-12 rounded-xl bg-secondary/10 flex items-center justify-center group-hover:bg-secondary/20 transition-colors">
                <span class="material-symbols-outlined text-secondary text-2xl" data-icon="add_card">add_card</span>
            </div>
            <div>
                <h3 class="font-headline-md text-headline-md text-on-surface">Cuotas Extraordinarias</h3>
                <p class="text-body-sm text-on-surface-variant">Cuotas especiales</p>
            </div>
        </div>
        <p class="text-body-sm text-on-surface-variant mb-lg">Resumen de cuotas extraordinarias y distribución.</p>
        <div class="flex items-center gap-2 text-secondary font-label-caps text-label-caps group-hover:gap-3 transition-all">
            <span>Ver Reporte</span>
            <span class="material-symbols-outlined text-lg" data-icon="arrow_forward">arrow_forward</span>
        </div>
    </a>

    <a href="#" class="report-link bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg hover:shadow-[0px_4px_12px_rgba(0,0,0,0.1)] transition-all group" data-report="bills-status">
        <div class="flex items-center gap-md mb-md">
            <div class="w-12 h-12 rounded-xl bg-secondary-container/30 flex items-center justify-center group-hover:bg-secondary-container/50 transition-colors">
                <span class="material-symbols-outlined text-on-secondary text-2xl" data-icon="description">description</span>
            </div>
            <div>
                <h3 class="font-headline-md text-headline-md text-on-surface">Facturas Pagadas y Pendientes</h3>
                <p class="text-body-sm text-on-surface-variant">Estado de facturación</p>
            </div>
        </div>
        <p class="text-body-sm text-on-surface-variant mb-lg">Consulta el estado de todas las facturas.</p>
        <div class="flex items-center gap-2 text-on-secondary font-label-caps text-label-caps group-hover:gap-3 transition-all">
            <span>Ver Reporte</span>
            <span class="material-symbols-outlined text-lg" data-icon="arrow_forward">arrow_forward</span>
        </div>
    </a>
</div>

@push('scripts')
<script>
(function() {
    var baseUrls = {
        'apartment-statement': '{{ route("reports.apartment-statement", ["apartment" => 1]) }}',
        'monthly-income': '{{ route("reports.monthly-income") }}',
        'monthly-expenses': '{{ route("reports.monthly-expenses") }}',
        'monthly-balance': '{{ route("reports.monthly-balance") }}',
        'debtors': '{{ route("reports.debtors") }}',
        'pending-payments': '{{ route("reports.pending-payments") }}',
        'gas-consumption': '{{ route("reports.gas-consumption") }}',
        'extra-charges': '{{ route("reports.extra-charges") }}',
        'bills-status': '{{ route("reports.bills-status") }}'
    };

    var condoSelect = document.getElementById('filter_condo');
    var monthSelect = document.getElementById('filter_month');
    var yearSelect = document.getElementById('filter_year');

    document.querySelectorAll('.report-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var report = this.dataset.report;
            var condoId = condoSelect.value;
            var month = monthSelect.value;
            var year = yearSelect.value;

            var url = baseUrls[report];

            if (report === 'apartment-statement') {
                url = url.replace('/1', '/' + condoId + '/apartments/first');
                fetch('{{ url("/admin/api/apartments-by-condominium") }}/' + condoId, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.length > 0) {
                        var aptUrl = '{{ route("reports.apartment-statement", ["apartment" => "APTID"]) }}'.replace('APTID', data[0].id);
                        window.open(aptUrl, '_blank');
                    } else {
                        alert('No hay apartamentos en este condominio.');
                    }
                });
                return;
            }

            var separator = url.indexOf('?') === -1 ? '?' : '&';
            url += separator + 'condominium_id=' + condoId + '&month=' + month + '&year=' + year;
            window.open(url, '_blank');
        });
    });
})();
</script>
@endpush
@endsection