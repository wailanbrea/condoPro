@extends($layout)

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ app()->getLocale() === 'es' ? 'Fondo del Condominio' : 'Condominium Fund' }}</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">{{ app()->getLocale() === 'es' ? 'Historial del Fondo' : 'Fund History' }}</h2>
        <p class="text-on-surface-variant">{{ $condominium->name ?? '' }}</p>
    </div>
    <form method="GET" action="{{ $isAdmin ? route('condominium-fund.history') : route('resident.condominium-fund') }}" class="flex items-center gap-sm">
        <select name="year" class="px-md py-sm border border-outline-variant rounded-lg bg-white text-on-surface font-body-md focus:ring-2 focus:ring-primary-container focus:border-primary transition-colors">
            @for($y = now()->year - 3; $y <= now()->year + 1; $y++)
                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
        <button type="submit" class="px-md py-sm bg-primary text-on-primary rounded-lg flex items-center gap-xs hover:bg-primary-container hover:text-on-primary-container transition-colors">
            <span class="material-symbols-outlined text-lg">filter_list</span>
        </button>
    </form>
</div>

@if($success)
    <div class="mb-lg px-lg py-md bg-[#E3FCEF] text-[#006644] rounded-lg flex items-center gap-2 border border-[#006644]/20">
        <span class="material-symbols-outlined" data-icon="check_circle">check_circle</span>
        {{ $success }}
    </div>
@endif

@if($error)
    <div class="mb-lg px-lg py-md bg-[#FFEBE6] text-[#BF2600] rounded-lg flex items-center gap-2 border border-[#BF2600]/20">
        <span class="material-symbols-outlined" data-icon="error">error</span>
        {{ $error }}
    </div>
@endif

{{-- Summary Card --}}
<div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] mb-xl border-l-4 {{ $finalBalance >= 0 ? 'border-secondary' : 'border-error' }}">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-lg">
            <div class="p-3 {{ $finalBalance >= 0 ? 'bg-secondary/10 text-secondary' : 'bg-error/10 text-error' }} rounded-xl">
                <span class="material-symbols-outlined text-3xl" data-icon="account_balance">account_balance</span>
            </div>
            <div>
                <p class="text-on-surface-variant font-label-caps text-label-caps">{{ app()->getLocale() === 'es' ? 'Fondo del Condominio' : 'Condominium Fund' }}</p>
                <h2 class="font-display-xl text-display-xl {{ $finalBalance >= 0 ? 'text-secondary' : 'text-error' }}">{{ $finalBalance >= 0 ? '' : '-' }}RD${{ number_format(abs($finalBalance), 2) }}</h2>
                <p class="text-body-sm text-on-surface-variant mt-xs">{{ app()->getLocale() === 'es' ? 'Balance acumulado al cierre de ' : 'Accumulated balance at end of ' }}{{ $year }}</p>
            </div>
        </div>
        <span class="px-4 py-2 {{ $finalBalance >= 0 ? 'bg-[#E3FCEF] text-[#006644]' : 'bg-[#FFEBE6] text-[#BF2600]' }} rounded-full text-sm font-bold uppercase tracking-wider">
            {{ $finalBalance >= 0 ? (app()->getLocale() === 'es' ? 'Fondo Positivo' : 'Positive Fund') : (app()->getLocale() === 'es' ? 'Déficit' : 'Deficit') }}
        </span>
    </div>
</div>

{{-- Withdraw Form (Admin Only) --}}
@if($isAdmin)
<div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] mb-xl border-l-4 border-error">
    <div class="flex items-center gap-md mb-lg">
        <div class="p-2 bg-error/10 rounded-lg text-error">
            <span class="material-symbols-outlined text-xl" data-icon="money_off">money_off</span>
        </div>
        <div>
            <h3 class="font-headline-md text-headline-md text-on-surface">{{ app()->getLocale() === 'es' ? 'Retirar Fondos' : 'Withdraw Funds' }}</h3>
            <p class="text-body-sm text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Registrar una salida de dinero del fondo del condominio' : 'Register a withdrawal from the condominium fund' }}</p>
        </div>
    </div>
    <form method="POST" action="{{ route('condominium-fund.withdraw') }}">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-lg">
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="withdraw_amount">{{ app()->getLocale() === 'es' ? 'Monto (RD$)' : 'Amount (RD$)' }} <span class="text-error">*</span></label>
                <input type="number" id="withdraw_amount" name="amount" step="0.01" min="1" required
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all"
                    placeholder="0.00">
            </div>
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="withdraw_date">{{ app()->getLocale() === 'es' ? 'Fecha' : 'Date' }} <span class="text-error">*</span></label>
                <input type="date" id="withdraw_date" name="movement_date" required value="{{ now()->format('Y-m-d') }}"
                    class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all">
            </div>
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="withdraw_reason">{{ app()->getLocale() === 'es' ? 'Motivo' : 'Reason' }} <span class="text-error">*</span></label>
                <div class="flex gap-sm">
                    <input type="text" id="withdraw_reason" name="description" required maxlength="255"
                        class="flex-1 px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all"
                        placeholder="{{ app()->getLocale() === 'es' ? 'Ej: Reparación urgente, pago contratista...' : 'E.g.: Emergency repair, contractor payment...' }}">
                    <button type="submit" class="px-lg py-2 bg-error text-white rounded-lg flex items-center gap-sm hover:brightness-110 transition-all whitespace-nowrap">
                        <span class="material-symbols-outlined text-lg">remove_circle</span>
                        {{ app()->getLocale() === 'es' ? 'Retirar' : 'Withdraw' }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endif

{{-- Monthly Table --}}
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] overflow-hidden mb-xl">
    <div class="p-lg border-b border-surface-container-low">
        <h3 class="font-headline-md text-headline-md text-on-surface">{{ app()->getLocale() === 'es' ? 'Detalle Mensual' : 'Monthly Detail' }} — {{ $year }}</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-low/50">
                <tr>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Mes' : 'Month' }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant text-right">{{ app()->getLocale() === 'es' ? 'Entradas' : 'Income' }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant text-right">{{ app()->getLocale() === 'es' ? 'Gastos' : 'Expenses' }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant text-right">{{ app()->getLocale() === 'es' ? 'Ajustes' : 'Adjustments' }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant text-right">{{ app()->getLocale() === 'es' ? 'Neto del Mes' : 'Monthly Net' }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant text-right">{{ app()->getLocale() === 'es' ? 'Balance Acumulado' : 'Running Balance' }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-container-low">
                @foreach($months as $m)
                    @php $hasData = $m['income'] > 0 || $m['expenses'] > 0 || $m['adjustments'] != 0; @endphp
                    <tr class="{{ $hasData ? 'hover:bg-surface-container-low transition-colors' : 'opacity-50' }}">
                        <td class="px-lg py-md font-bold text-on-surface">{{ $m['name'] }}</td>
                        <td class="px-lg py-md text-right font-mono-data {{ $m['income'] > 0 ? 'text-secondary' : 'text-on-surface-variant' }}">{{ $m['income'] > 0 ? 'RD$' . number_format($m['income'], 0) : '—' }}</td>
                        <td class="px-lg py-md text-right font-mono-data {{ $m['expenses'] > 0 ? 'text-error' : 'text-on-surface-variant' }}">{{ $m['expenses'] > 0 ? '-RD$' . number_format($m['expenses'], 0) : '—' }}</td>
                        <td class="px-lg py-md text-right font-mono-data {{ $m['adjustments'] > 0 ? 'text-primary' : ($m['adjustments'] < 0 ? 'text-error' : 'text-on-surface-variant') }}">{{ $m['adjustments'] > 0 ? '+RD$' . number_format($m['adjustments'], 0) : ($m['adjustments'] < 0 ? 'RD$' . number_format(abs($m['adjustments']), 0) : '—') }}</td>
                        <td class="px-lg py-md text-right font-mono-data font-bold {{ $m['net'] >= 0 ? 'text-secondary' : 'text-error' }}">{{ $m['net'] >= 0 ? '+' : '' }}RD${{ number_format($m['net'], 0) }}</td>
                        <td class="px-lg py-md text-right font-mono-data font-bold {{ $m['balance'] >= 0 ? 'text-secondary' : 'text-error' }}">{{ $m['balance'] >= 0 ? '' : '-' }}RD${{ number_format(abs($m['balance']), 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-surface-container-low/30 border-t-2 border-outline-variant">
                <tr>
                    <td class="px-lg py-lg font-bold text-on-surface">{{ app()->getLocale() === 'es' ? 'Total del Año' : 'Year Total' }}</td>
                    <td class="px-lg py-lg text-right font-mono-data font-bold text-secondary">RD${{ number_format(collect($months)->sum('income'), 0) }}</td>
                    <td class="px-lg py-lg text-right font-mono-data font-bold text-error">-RD${{ number_format(collect($months)->sum('expenses'), 0) }}</td>
                    <td class="px-lg py-lg text-right font-mono-data font-bold text-primary">{{ collect($months)->sum('adjustments') >= 0 ? '+' : '' }}RD${{ number_format(collect($months)->sum('adjustments'), 0) }}</td>
                    <td class="px-lg py-lg text-right font-mono-data font-bold {{ collect($months)->sum('net') >= 0 ? 'text-secondary' : 'text-error' }}">{{ collect($months)->sum('net') >= 0 ? '+' : '' }}RD${{ number_format(abs(collect($months)->sum('net')), 0) }}</td>
                    <td class="px-lg py-lg text-right font-mono-data font-bold {{ $finalBalance >= 0 ? 'text-secondary' : 'text-error' }}">{{ $finalBalance >= 0 ? '' : '-' }}RD${{ number_format(abs($finalBalance), 0) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Detailed Movements --}}
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] overflow-hidden mb-xl">
    <div class="p-lg border-b border-surface-container-low">
        <h3 class="font-headline-md text-headline-md text-on-surface">{{ app()->getLocale() === 'es' ? 'Movimientos Detallados' : 'Detailed Movements' }}</h3>
        <p class="text-body-sm text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Retiros, depósitos y ajustes' : 'Withdrawals, deposits and adjustments' }}</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-low/50">
                <tr>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Fecha' : 'Date' }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Tipo' : 'Type' }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Motivo / Descripción' : 'Reason / Description' }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant text-right">{{ app()->getLocale() === 'es' ? 'Monto' : 'Amount' }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Registrado por' : 'Registered by' }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-container-low">
                @forelse($movements as $movement)
                    @php
                        $isDebit = $movement->movement_type === 'expense' || $movement->amount < 0;
                        $displayAmount = $isDebit ? -abs($movement->amount) : abs($movement->amount);
                        $typeLabel = match($movement->movement_type) {
                            'income' => app()->getLocale() === 'es' ? 'Ingreso' : 'Income',
                            'expense' => app()->getLocale() === 'es' ? 'Egreso' : 'Expense',
                            'adjustment' => $movement->amount < 0
                                ? (app()->getLocale() === 'es' ? 'Retiro' : 'Withdrawal')
                                : (app()->getLocale() === 'es' ? 'Depósito/Ajuste' : 'Deposit/Adjustment'),
                            default => $movement->movement_type,
                        };
                        $typeBadge = match($movement->movement_type) {
                            'income' => 'bg-secondary/10 text-secondary',
                            'expense' => 'bg-error/10 text-error',
                            'adjustment' => $movement->amount < 0 ? 'bg-error/10 text-error' : 'bg-primary/10 text-primary',
                            default => 'bg-surface-container-low text-on-surface-variant',
                        };
                    @endphp
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-lg py-md text-on-surface-variant">{{ $movement->movement_date ? $movement->movement_date->format('d M Y') : '—' }}</td>
                        <td class="px-lg py-md"><span class="px-2 py-1 {{ $typeBadge }} rounded text-xs font-bold uppercase tracking-wider">{{ $typeLabel }}</span></td>
                        <td class="px-lg py-md text-on-surface">{{ $movement->description }}</td>
                        <td class="px-lg py-md text-right font-mono-data font-bold {{ $isDebit ? 'text-error' : 'text-secondary' }}">{{ $isDebit ? '-' : '+' }}RD${{ number_format(abs($displayAmount), 2) }}</td>
                        <td class="px-lg py-md text-on-surface-variant text-body-sm">{{ $movement->creator?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-lg py-xl text-center text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'No hay movimientos registrados' : 'No movements registered' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Legend --}}
<div class="flex flex-wrap gap-lg text-body-sm text-on-surface-variant">
    <div class="flex items-center gap-sm">
        <span class="w-3 h-3 rounded-full bg-secondary"></span>
        <span>{{ app()->getLocale() === 'es' ? 'Entradas (cobros confirmados)' : 'Income (confirmed payments)' }}</span>
    </div>
    <div class="flex items-center gap-sm">
        <span class="w-3 h-3 rounded-full bg-error"></span>
        <span>{{ app()->getLocale() === 'es' ? 'Gastos (egresos)' : 'Expenses (outflows)' }}</span>
    </div>
    <div class="flex items-center gap-sm">
        <span class="w-3 h-3 rounded-full bg-primary"></span>
        <span>{{ app()->getLocale() === 'es' ? 'Ajustes (aperturas, retiros)' : 'Adjustments (opening balances, withdrawals)' }}</span>
    </div>
    <div class="flex items-center gap-sm ml-auto">
        <span class="material-symbols-outlined text-lg">info</span>
        <span>{{ app()->getLocale() === 'es' ? 'El balance acumulado incluye datos de años anteriores' : 'Running balance includes prior years data' }}</span>
    </div>
</div>
@endsection