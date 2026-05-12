@extends($layout)

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ app()->getLocale() === 'es' ? 'Fondo del Condominio' : 'Condominium Fund' }}</span>
</nav>

{{-- HEADER --}}
<div class="flex flex-col md:flex-row md:justify-between md:items-end gap-md mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">{{ app()->getLocale() === 'es' ? 'Historial del Fondo' : 'Fund History' }}</h2>
        <p class="text-on-surface-variant">{{ $condominium->name ?? '' }}</p>
    </div>
    <div class="flex items-center gap-md flex-wrap">
        <form method="GET" action="{{ $isAdmin ? route('condominium-fund.history') : route('resident.condominium-fund') }}" class="flex items-center gap-sm">
            <select name="year" class="px-md py-sm border border-outline-variant rounded-lg bg-white text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary transition-colors">
                @for($y = now()->year - 3; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="px-md py-sm bg-white border border-outline-variant rounded-lg flex items-center gap-xs hover:bg-surface-container-low transition-colors">
                <span class="material-symbols-outlined text-lg">filter_list</span>
            </button>
        </form>
        @if($isAdmin)
        <button onclick="document.getElementById('withdrawModal').classList.remove('hidden')" class="px-md py-sm bg-error text-white rounded-lg flex items-center gap-xs hover:brightness-110 transition-all">
            <span class="material-symbols-outlined text-lg">remove_circle</span>
            {{ app()->getLocale() === 'es' ? 'Retirar fondos' : 'Withdraw funds' }}
        </button>
        @endif
    </div>
</div>

{{-- Flash messages --}}
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

{{-- SUMMARY CARDS --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-gutter mb-xl">
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 {{ $finalBalance >= 0 ? 'border-[#006644]' : 'border-error' }}">
        <p class="text-label-caps text-on-surface-variant font-label-caps text-[11px] mb-xs">{{ app()->getLocale() === 'es' ? 'Fondo actual' : 'Current fund' }}</p>
        <p class="font-headline-lg text-headline-lg {{ $finalBalance >= 0 ? 'text-[#006644]' : 'text-error' }} font-bold">{{ $finalBalance >= 0 ? '' : '-' }}RD${{ number_format(abs($finalBalance), 2) }}</p>
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-[#006644]">
        <p class="text-label-caps text-on-surface-variant font-label-caps text-[11px] mb-xs">{{ app()->getLocale() === 'es' ? 'Entradas del año' : 'Year income' }}</p>
        <p class="font-headline-lg text-headline-lg text-[#006644] font-bold">RD${{ number_format($totalIncome, 2) }}</p>
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-error">
        <p class="text-label-caps text-on-surface-variant font-label-caps text-[11px] mb-xs">{{ app()->getLocale() === 'es' ? 'Gastos del año' : 'Year expenses' }}</p>
        <p class="font-headline-lg text-headline-lg text-error font-bold">-RD${{ number_format($totalExpenses, 2) }}</p>
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 {{ ($totalIncome - $totalExpenses) >= 0 ? 'border-primary' : 'border-error' }}">
        <p class="text-label-caps text-on-surface-variant font-label-caps text-[11px] mb-xs">{{ app()->getLocale() === 'es' ? 'Neto del año' : 'Year net' }}</p>
        <p class="font-headline-lg text-headline-lg {{ ($totalIncome - $totalExpenses) >= 0 ? 'text-primary' : 'text-error' }} font-bold">{{ ($totalIncome - $totalExpenses) >= 0 ? '+' : '-' }}RD${{ number_format(abs($totalIncome - $totalExpenses), 2) }}</p>
    </div>
</div>

{{-- MONTHLY TABLE --}}
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] overflow-hidden mb-xl">
    <div class="p-lg border-b border-surface-container-low flex justify-between items-center flex-wrap gap-md">
        <h3 class="font-headline-md text-headline-md text-on-surface">{{ app()->getLocale() === 'es' ? 'Detalle Mensual' : 'Monthly Detail' }} — {{ $year }}</h3>
        <label class="flex items-center gap-sm text-body-sm text-on-surface-variant cursor-pointer select-none">
            <input type="checkbox" id="showEmptyMonths" class="rounded border-outline-variant text-primary focus:ring-primary-container" onchange="toggleEmptyMonths()">
            {{ app()->getLocale() === 'es' ? 'Mostrar meses sin movimientos' : 'Show months without movements' }}
        </label>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse" id="monthlyTable">
            <thead>
                <tr class="bg-surface-container-low/80 border-b-2 border-primary">
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Mes' : 'Month' }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant text-right">{{ app()->getLocale() === 'es' ? 'Entradas' : 'Income' }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant text-right">{{ app()->getLocale() === 'es' ? 'Gastos' : 'Expenses' }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant text-right">{{ app()->getLocale() === 'es' ? 'Neto del Mes' : 'Monthly Net' }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant text-right">{{ app()->getLocale() === 'es' ? 'Balance Acum.' : 'Running Bal.' }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant text-center">{{ app()->getLocale() === 'es' ? 'Acción' : 'Action' }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @foreach($months as $m)
                <tr class="month-row hover:bg-surface-container-lowest/50 transition-colors {{ !$m['has_data'] ? 'empty-month opacity-50' : '' }}" data-month="{{ $m['month'] }}">
                    <td class="px-lg py-md font-bold text-on-surface whitespace-nowrap">
                        {{ $m['name'] }}
                        @if($m['has_adjustments'])
                            <span class="ml-2 px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full text-[10px] font-bold uppercase tracking-wider">{{ app()->getLocale() === 'es' ? 'Con ajustes' : 'Adj' }}</span>
                        @endif
                    </td>
                    <td class="px-lg py-md text-right font-mono-data {{ $m['income'] > 0 ? 'text-[#006644] font-bold' : 'text-on-surface-variant' }}">{{ $m['income'] > 0 ? 'RD$' . number_format($m['income'], 0) : '—' }}</td>
                    <td class="px-lg py-md text-right font-mono-data {{ $m['expenses'] > 0 ? 'text-error font-bold' : 'text-on-surface-variant' }}">{{ $m['expenses'] > 0 ? '-RD$' . number_format($m['expenses'], 0) : '—' }}</td>
                    <td class="px-lg py-md text-right font-mono-data font-bold {{ $m['net'] >= 0 ? 'text-[#006644]' : 'text-error' }}">{{ $m['net'] >= 0 ? '+' : '' }}RD${{ number_format($m['net'], 0) }}</td>
                    <td class="px-lg py-md text-right font-mono-data font-bold {{ $m['balance'] >= 0 ? 'text-primary' : 'text-error' }}">{{ $m['balance'] >= 0 ? '' : '-' }}RD${{ number_format(abs($m['balance']), 0) }}</td>
                    <td class="px-lg py-md text-center">
                        @if($m['has_data'])
                        <button onclick="toggleMonth({{ $m['month'] }})" class="text-primary text-body-sm font-semibold hover:underline flex items-center justify-center gap-1 mx-auto">
                            <span id="toggleIcon{{ $m['month'] }}" class="material-symbols-outlined text-[18px]">expand_more</span>
                            <span id="toggleText{{ $m['month'] }}">{{ app()->getLocale() === 'es' ? 'Ver detalle' : 'Details' }}</span>
                        </button>
                        @else
                            <span class="text-on-surface-variant text-body-sm">—</span>
                        @endif
                    </td>
                </tr>
                @if($m['has_data'])
                <tr class="detail-row hidden" id="detailRow{{ $m['month'] }}" data-month="{{ $m['month'] }}">
                    <td colspan="6" class="p-0">
                        <div class="bg-surface-container-lowest/30 px-lg py-lg border-t border-outline-variant">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter">
                                {{-- INCOME --}}
                                <div class="bg-white rounded-lg border border-outline-variant p-md">
                                    <h4 class="font-label-caps text-label-caps text-[#006644] mb-md flex items-center gap-xs">
                                        <span class="material-symbols-outlined text-[16px]">trending_up</span>
                                        {{ app()->getLocale() === 'es' ? 'ENTRADAS' : 'INCOME' }}
                                    </h4>
                                    @if(!empty($m['details']['payments']))
                                    <table class="w-full text-left text-body-sm">
                                        <thead><tr class="text-on-surface-variant text-[11px] border-b border-outline-variant"><th class="py-xs">{{ app()->getLocale() === 'es' ? 'Fecha' : 'Date' }}</th><th class="py-xs">{{ app()->getLocale() === 'es' ? 'Concepto' : 'Concept' }}</th><th class="py-xs hidden sm:table-cell">{{ app()->getLocale() === 'es' ? 'Apto' : 'Apt' }}</th><th class="py-xs text-right">{{ app()->getLocale() === 'es' ? 'Monto' : 'Amount' }}</th></tr></thead>
                                        <tbody class="divide-y divide-outline-variant/50">
                                            @foreach($m['details']['payments'] as $p)
                                            <tr>
                                                <td class="py-xs text-on-surface-variant whitespace-nowrap">{{ $p['date'] ? \Carbon\Carbon::parse($p['date'])->format('d M') : '—' }}</td>
                                                <td class="py-xs">{{ $p['concept'] }}</td>
                                                <td class="py-xs hidden sm:table-cell">{{ $p['apartment'] }}</td>
                                                <td class="py-xs text-right font-mono-data text-[#006644] font-bold">RD${{ number_format($p['amount'], 0) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot><tr class="border-t-2 border-[#006644]/30"><td colspan="3" class="py-sm font-bold text-[#006644]">{{ app()->getLocale() === 'es' ? 'Total' : 'Total' }}</td><td class="py-sm text-right font-mono-data font-bold text-[#006644]">RD${{ number_format($m['income'], 0) }}</td></tr></tfoot>
                                    </table>
                                    @else
                                    <p class="text-on-surface-variant text-body-sm italic">{{ app()->getLocale() === 'es' ? 'Sin entradas este mes' : 'No income this month' }}</p>
                                    @endif
                                </div>

                                {{-- EXPENSES --}}
                                <div class="bg-white rounded-lg border border-outline-variant p-md">
                                    <h4 class="font-label-caps text-label-caps text-error mb-md flex items-center gap-xs">
                                        <span class="material-symbols-outlined text-[16px]">trending_down</span>
                                        {{ app()->getLocale() === 'es' ? 'GASTOS' : 'EXPENSES' }}
                                    </h4>
                                    @if(!empty($m['details']['expenses']))
                                    <table class="w-full text-left text-body-sm">
                                        <thead><tr class="text-on-surface-variant text-[11px] border-b border-outline-variant"><th class="py-xs">{{ app()->getLocale() === 'es' ? 'Fecha' : 'Date' }}</th><th class="py-xs">{{ app()->getLocale() === 'es' ? 'Categoría' : 'Category' }}</th><th class="py-xs">{{ app()->getLocale() === 'es' ? 'Concepto' : 'Concept' }}</th><th class="py-xs text-right">{{ app()->getLocale() === 'es' ? 'Monto' : 'Amount' }}</th></tr></thead>
                                        <tbody class="divide-y divide-outline-variant/50">
                                            @foreach($m['details']['expenses'] as $e)
                                            <tr>
                                                <td class="py-xs text-on-surface-variant whitespace-nowrap">{{ $e['date'] ? \Carbon\Carbon::parse($e['date'])->format('d M') : '—' }}</td>
                                                <td class="py-xs"><span class="px-1.5 py-0.5 bg-error/10 text-error rounded text-[10px] font-bold uppercase">{{ $e['category'] }}</span></td>
                                                <td class="py-xs">{{ $e['concept'] }}</td>
                                                <td class="py-xs text-right font-mono-data text-error font-bold">-RD${{ number_format($e['amount'], 0) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot><tr class="border-t-2 border-error/30"><td colspan="3" class="py-sm font-bold text-error">{{ app()->getLocale() === 'es' ? 'Total' : 'Total' }}</td><td class="py-sm text-right font-mono-data font-bold text-error">-RD${{ number_format($m['expenses'], 0) }}</td></tr></tfoot>
                                    </table>
                                    @else
                                    <p class="text-on-surface-variant text-body-sm italic">{{ app()->getLocale() === 'es' ? 'Sin gastos este mes' : 'No expenses this month' }}</p>
                                    @endif
                                </div>

                                {{-- ADJUSTMENTS --}}
                                <div class="bg-white rounded-lg border border-outline-variant p-md">
                                    <h4 class="font-label-caps text-label-caps text-amber-700 mb-md flex items-center gap-xs">
                                        <span class="material-symbols-outlined text-[16px]">tune</span>
                                        {{ app()->getLocale() === 'es' ? 'AJUSTES' : 'ADJUSTMENTS' }}
                                    </h4>
                                    @if(!empty($m['details']['adjustments']))
                                    <table class="w-full text-left text-body-sm">
                                        <thead><tr class="text-on-surface-variant text-[11px] border-b border-outline-variant"><th class="py-xs">{{ app()->getLocale() === 'es' ? 'Fecha' : 'Date' }}</th><th class="py-xs">{{ app()->getLocale() === 'es' ? 'Tipo' : 'Type' }}</th><th class="py-xs">{{ app()->getLocale() === 'es' ? 'Descripción' : 'Description' }}</th><th class="py-xs text-right">{{ app()->getLocale() === 'es' ? 'Monto' : 'Amount' }}</th></tr></thead>
                                        <tbody class="divide-y divide-outline-variant/50">
                                            @foreach($m['details']['adjustments'] as $a)
                                            <tr>
                                                <td class="py-xs text-on-surface-variant whitespace-nowrap">{{ $a['date'] ? \Carbon\Carbon::parse($a['date'])->format('d M') : '—' }}</td>
                                                <td class="py-xs"><span class="px-1.5 py-0.5 {{ $a['amount'] < 0 ? 'bg-error/10 text-error' : 'bg-primary/10 text-primary' }} rounded text-[10px] font-bold uppercase">{{ $a['type'] }}</span></td>
                                                <td class="py-xs">{{ $a['description'] }}</td>
                                                <td class="py-xs text-right font-mono-data font-bold {{ $a['amount'] < 0 ? 'text-error' : 'text-primary' }}">{{ $a['amount'] >= 0 ? '+' : '' }}RD${{ number_format($a['amount'], 0) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot><tr class="border-t-2 border-amber-500/30"><td colspan="3" class="py-sm font-bold text-amber-700">{{ app()->getLocale() === 'es' ? 'Total ajustes' : 'Adj. total' }}</td><td class="py-sm text-right font-mono-data font-bold {{ $m['adjustments'] >= 0 ? 'text-primary' : 'text-error' }}">{{ $m['adjustments'] >= 0 ? '+' : '' }}RD${{ number_format($m['adjustments'], 0) }}</td></tr></tfoot>
                                    </table>
                                    @else
                                    <p class="text-on-surface-variant text-body-sm italic">{{ app()->getLocale() === 'es' ? 'Sin ajustes este mes' : 'No adjustments this month' }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-primary/10 border-t-2 border-primary">
                    <td class="px-lg py-lg font-bold text-primary">{{ app()->getLocale() === 'es' ? 'Total del Año' : 'Year Total' }}</td>
                    <td class="px-lg py-lg text-right font-mono-data font-bold text-[#006644]">RD${{ number_format($totalIncome, 0) }}</td>
                    <td class="px-lg py-lg text-right font-mono-data font-bold text-error">-RD${{ number_format($totalExpenses, 0) }}</td>
                    <td class="px-lg py-lg text-right font-mono-data font-bold {{ ($totalIncome - $totalExpenses) >= 0 ? 'text-[#006644]' : 'text-error' }}">{{ ($totalIncome - $totalExpenses) >= 0 ? '+' : '' }}RD${{ number_format(abs($totalIncome - $totalExpenses), 0) }}</td>
                    <td class="px-lg py-lg text-right font-mono-data font-bold {{ $finalBalance >= 0 ? 'text-primary' : 'text-error' }}">{{ $finalBalance >= 0 ? '' : '-' }}RD${{ number_format(abs($finalBalance), 0) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- WITHDRAW MODAL --}}
@if($isAdmin)
<div id="withdrawModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl p-lg max-w-lg w-full shadow-2xl">
        <div class="flex items-center gap-md mb-lg">
            <div class="p-2 bg-error/10 rounded-lg text-error">
                <span class="material-symbols-outlined text-xl" data-icon="money_off">money_off</span>
            </div>
            <div>
                <h3 class="font-headline-md text-headline-md text-on-surface">{{ app()->getLocale() === 'es' ? 'Retirar Fondos' : 'Withdraw Funds' }}</h3>
                <p class="text-body-sm text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Registrar una salida de dinero del fondo' : 'Register a fund withdrawal' }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('condominium-fund.withdraw') }}">
            @csrf
            <div class="grid grid-cols-1 gap-lg mb-lg">
                <div>
                    <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="withdraw_amount">{{ app()->getLocale() === 'es' ? 'Monto (RD$)' : 'Amount (RD$)' }} <span class="text-error">*</span></label>
                    <input type="number" id="withdraw_amount" name="amount" step="0.01" min="1" required
                        class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all" placeholder="0.00">
                </div>
                <div>
                    <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="withdraw_date">{{ app()->getLocale() === 'es' ? 'Fecha' : 'Date' }} <span class="text-error">*</span></label>
                    <input type="date" id="withdraw_date" name="movement_date" required value="{{ now()->format('Y-m-d') }}"
                        class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all">
                </div>
                <div>
                    <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="withdraw_reason">{{ app()->getLocale() === 'es' ? 'Motivo' : 'Reason' }} <span class="text-error">*</span></label>
                    <input type="text" id="withdraw_reason" name="description" required maxlength="255"
                        class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all"
                        placeholder="{{ app()->getLocale() === 'es' ? 'Ej: Reparación urgente...' : 'E.g.: Emergency repair...' }}">
                </div>
            </div>
            <div class="flex justify-end gap-md">
                <button type="button" onclick="document.getElementById('withdrawModal').classList.add('hidden')" class="px-lg py-md bg-white border border-outline-variant rounded-lg hover:bg-surface-container-low transition-colors">{{ app()->getLocale() === 'es' ? 'Cancelar' : 'Cancel' }}</button>
                <button type="submit" class="px-lg py-md bg-error text-white rounded-lg hover:brightness-110 transition-all">{{ app()->getLocale() === 'es' ? 'Confirmar retiro' : 'Confirm withdrawal' }}</button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
(function() {
    var openMonth = null;

    window.toggleMonth = function(month) {
        var detailRow = document.getElementById('detailRow' + month);
        var icon = document.getElementById('toggleIcon' + month);
        var text = document.getElementById('toggleText' + month);

        if (!detailRow || !icon || !text) return;

        if (openMonth && openMonth !== month) {
            var prevRow = document.getElementById('detailRow' + openMonth);
            var prevIcon = document.getElementById('toggleIcon' + openMonth);
            var prevText = document.getElementById('toggleText' + openMonth);
            if (prevRow) { prevRow.classList.add('hidden'); }
            if (prevIcon) { prevIcon.textContent = 'expand_more'; }
            if (prevText) { prevText.textContent = '{{ app()->getLocale() === 'es' ? 'Ver detalle' : 'Details' }}'; }
        }

        if (detailRow.classList.contains('hidden')) {
            detailRow.classList.remove('hidden');
            icon.textContent = 'expand_less';
            text.textContent = '{{ app()->getLocale() === 'es' ? 'Ocultar detalle' : 'Hide details' }}';
            openMonth = month;
        } else {
            detailRow.classList.add('hidden');
            icon.textContent = 'expand_more';
            text.textContent = '{{ app()->getLocale() === 'es' ? 'Ver detalle' : 'Details' }}';
            openMonth = null;
        }
    };

    window.toggleEmptyMonths = function() {
        var show = document.getElementById('showEmptyMonths').checked;
        var rows = document.querySelectorAll('.empty-month');
        rows.forEach(function(row) {
            row.style.display = show ? '' : 'none';
        });
    };

    // Close modal on backdrop click
    var modal = document.getElementById('withdrawModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) modal.classList.add('hidden');
        });
    }
})();
</script>
@endpush
@endsection
