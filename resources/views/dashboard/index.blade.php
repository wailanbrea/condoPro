@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ __('messages.nav.dashboard') }}</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">{{ __('messages.dashboard.title') }}</h2>
        <p class="text-on-surface-variant">{{ __('messages.dashboard.subtitle') }}</p>
    </div>
    <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-sm">
        <select name="month" id="filterMonth" class="px-md py-sm border border-outline-variant rounded-lg bg-white text-on-surface font-body-md focus:ring-2 focus:ring-primary-container focus:border-primary transition-colors">
            @foreach($availableMonths as $m => $name)
                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ ucfirst($name) }}</option>
            @endforeach
        </select>
        <select name="year" id="filterYear" class="px-md py-sm border border-outline-variant rounded-lg bg-white text-on-surface font-body-md focus:ring-2 focus:ring-primary-container focus:border-primary transition-colors">
            @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
        <button type="submit" class="px-md py-sm bg-primary text-on-primary rounded-lg flex items-center gap-xs hover:bg-primary-container hover:text-on-primary-container transition-colors">
            <span class="material-symbols-outlined text-lg">filter_list</span>
        </button>
    </form>
</div>

{{-- Stats Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-gutter mb-xl">
    {{-- Facturado --}}
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-primary">
        <div class="flex justify-between items-start mb-md">
            <div class="p-2 bg-primary/10 rounded-lg text-primary">
                <span class="material-symbols-outlined" data-icon="receipt_long">receipt_long</span>
            </div>
            @if($billedChange !== null)
                <span class="text-{{ $billedChange >= 0 ? 'secondary' : 'error' }} font-bold text-body-sm">{{ $billedChange >= 0 ? '+' : '' }}{{ $billedChange }}%</span>
            @elseif(!$hasPrevData)
                <span class="text-on-surface-variant text-body-xs italic">{{ app()->getLocale() === 'es' ? 'Sin histórico' : 'No history' }}</span>
            @endif
        </div>
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ app()->getLocale() === 'es' ? 'Facturado' : 'Billed' }}</p>
        <h3 class="font-headline-lg text-headline-lg text-primary">RD${{ number_format($totalBilled, 0) }}</h3>
    </div>
    {{-- Cobrado --}}
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-secondary">
        <div class="flex justify-between items-start mb-md">
            <div class="p-2 bg-secondary/10 rounded-lg text-secondary">
                <span class="material-symbols-outlined" data-icon="payments">payments</span>
            </div>
            @if($collectedChange !== null)
                <span class="text-{{ $collectedChange >= 0 ? 'secondary' : 'error' }} font-bold text-body-sm">{{ $collectedChange >= 0 ? '+' : '' }}{{ $collectedChange }}%</span>
            @elseif(!$hasPrevData)
                <span class="text-on-surface-variant text-body-xs italic">{{ app()->getLocale() === 'es' ? 'Sin histórico' : 'No history' }}</span>
            @endif
        </div>
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ app()->getLocale() === 'es' ? 'Cobrado' : 'Collected' }}</p>
        <h3 class="font-headline-lg text-headline-lg text-secondary">RD${{ number_format($totalCollected, 0) }}</h3>
    </div>
    {{-- Pendiente --}}
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-warning">
        <div class="flex justify-between items-start mb-md">
            <div class="p-2 bg-amber-100 rounded-lg text-amber-700">
                <span class="material-symbols-outlined" data-icon="hourglass_top">hourglass_top</span>
            </div>
        </div>
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ app()->getLocale() === 'es' ? 'Pendiente' : 'Pending' }}</p>
        <h3 class="font-headline-lg text-headline-lg text-amber-700">RD${{ number_format($totalPending, 0) }}</h3>
    </div>
    {{-- Gastos --}}
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-error">
        <div class="flex justify-between items-start mb-md">
            <div class="p-2 bg-error/10 rounded-lg text-error">
                <span class="material-symbols-outlined" data-icon="trending_down">trending_down</span>
            </div>
            @if($expensesChange !== null)
                <span class="text-error font-bold text-body-sm">{{ $expensesChange >= 0 ? '+' : '' }}{{ $expensesChange }}%</span>
            @elseif(!$hasPrevData)
                <span class="text-on-surface-variant text-body-xs italic">{{ app()->getLocale() === 'es' ? 'Sin histórico' : 'No history' }}</span>
            @endif
        </div>
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.dashboard.expenses') }}</p>
        <h3 class="font-headline-lg text-headline-lg text-on-surface">RD${{ number_format($totalExpenses, 0) }}</h3>
    </div>
    {{-- Fondo del Condominio (global, not per-month) --}}
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 {{ $condoFund >= 0 ? 'border-secondary' : 'border-error' }}">
        <div class="flex justify-between items-start mb-md">
            <div class="p-2 {{ $condoFund >= 0 ? 'bg-secondary/10 text-secondary' : 'bg-error/10 text-error' }} rounded-lg">
                <span class="material-symbols-outlined" data-icon="account_balance">account_balance</span>
            </div>
            <span class="text-on-surface-variant font-bold text-body-sm {{ $condoFund >= 0 ? 'text-secondary' : 'text-error' }}">{{ $condoFund >= 0 ? (app()->getLocale() === 'es' ? 'FONDO POSITIVO' : 'POSITIVE FUND') : (app()->getLocale() === 'es' ? 'DÉFICIT' : 'DEFICIT') }}</span>
        </div>
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ app()->getLocale() === 'es' ? 'Fondo del Condominio' : 'Condominium Fund' }}</p>
        <h3 class="font-headline-lg text-headline-lg {{ $condoFund >= 0 ? 'text-secondary' : 'text-error' }}">{{ $condoFund >= 0 ? '' : '-' }}RD${{ number_format(abs($condoFund), 0) }}</h3>
        <p class="text-body-xs text-on-surface-variant mt-xs">{{ app()->getLocale() === 'es' ? 'Acumulado histórico · Cobrado − Gastos + Ajustes' : 'All-time · Collected − Expenses + Adjustments' }}</p>
    </div>
    {{-- Aptos Deuda + Pagos Pendientes --}}
    <div class="space-y-gutter">
        <div class="bg-white p-md rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-tertiary-container">
            <div class="flex items-center gap-sm">
                <div class="p-1 bg-tertiary/10 rounded text-tertiary">
                    <span class="material-symbols-outlined text-lg" data-icon="domain_disabled">domain_disabled</span>
                </div>
                <div>
                    <p class="text-on-surface-variant font-label-caps text-label-caps leading-tight">{{ __('messages.dashboard.apts_in_debt') }}</p>
                    <h3 class="font-headline-md text-headline-md text-on-surface">{{ $aptsInDebt }}</h3>
                </div>
            </div>
        </div>
        <div class="bg-white p-md rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-warning">
            <div class="flex items-center gap-sm">
                <div class="p-1 bg-amber-100 rounded text-amber-700">
                    <span class="material-symbols-outlined text-lg" data-icon="pending_actions">pending_actions</span>
                </div>
                <div>
                    <p class="text-on-surface-variant font-label-caps text-label-caps leading-tight">{{ __('messages.dashboard.pending_payments') }}</p>
                    <h3 class="font-headline-md text-headline-md text-on-surface">{{ $pendingPayments }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Charts Section --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter mb-xl">
    {{-- Ingresos vs Gastos Bar Chart --}}
    <div class="lg:col-span-2 bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] overflow-hidden">
        <div class="flex justify-between items-center mb-lg">
            <h4 class="font-headline-md text-headline-md">{{ app()->getLocale() === 'es' ? 'Facturado vs Cobrado vs Gastos' : 'Billed vs Collected vs Expenses' }}</h4>
            <select class="bg-surface-container-low border-none rounded-lg text-body-sm px-4">
                <option>{{ app()->getLocale() === 'es' ? 'Últimos 6 meses' : 'Last 6 months' }}</option>
            </select>
        </div>
        @php
            $yMax = $maxVal ?? 1;
            $steps = 5;
        @endphp
        <div class="flex" style="height: 260px;">
            <div class="flex flex-col justify-between py-0 pr-2 text-right" style="min-width: 48px;">
                @for ($s = $steps; $s >= 0; $s--)
                    @php $val = round(($yMax / $steps) * $s); @endphp
                    <span class="text-body-xs text-on-surface-variant font-mono-data leading-none">{{ $val >= 1000 ? number_format($val / 1000, 0) . 'k' : number_format($val, 0) }}</span>
                @endfor
            </div>
            <div class="flex-1 relative">
                @for ($s = 0; $s <= $steps; $s++)
                    <div class="absolute left-0 right-0 border-t border-dashed border-outline-variant/30" style="top: {{ ($s / $steps) * 100 }}%;"></div>
                @endfor
                <div class="absolute inset-0 flex items-end justify-around gap-3 pb-6">
                    @foreach ($monthlyData ?? [] as $mth)
                        <div class="flex-1 flex flex-col items-center min-w-0 h-full">
                            <div class="flex-1 w-full flex gap-0.5 items-end">
                                <div class="bg-primary flex-1 rounded-t-sm transition-all duration-500" style="height: {{ max(2, $mth['billed_pct'] ?? 0) }}%;" title="{{ app()->getLocale() === 'es' ? 'Facturado' : 'Billed' }}: RD${{ number_format($mth['billed'] ?? 0, 0) }}"></div>
                                <div class="bg-secondary flex-1 rounded-t-sm transition-all duration-500" style="height: {{ max(2, $mth['collected_pct'] ?? 0) }}%;" title="{{ app()->getLocale() === 'es' ? 'Cobrado' : 'Collected' }}: RD${{ number_format($mth['collected'] ?? 0, 0) }}"></div>
                                <div class="bg-error/70 flex-1 rounded-t-sm transition-all duration-500" style="height: {{ max(2, $mth['expense_pct'] ?? 0) }}%;" title="{{ __('messages.dashboard.expenses') }}: RD${{ number_format($mth['expense'] ?? 0, 0) }}"></div>
                            </div>
                            <span class="text-body-xs text-on-surface-variant mt-1 truncate w-full text-center">{{ $mth['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="flex justify-center gap-lg mt-md">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-sm bg-primary"></span>
                <span class="text-body-sm text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Facturado' : 'Billed' }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-sm bg-secondary"></span>
                <span class="text-body-sm text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Cobrado' : 'Collected' }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-sm bg-error/70"></span>
                <span class="text-body-sm text-on-surface-variant">{{ __('messages.dashboard.expenses') }}</span>
            </div>
        </div>
    </div>

    {{-- Collection Status Donut --}}
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] flex flex-col overflow-hidden">
        <h4 class="font-headline-md text-headline-md mb-xs">{{ __('messages.dashboard.collection_status') }}</h4>
        <p class="text-body-sm text-on-surface-variant mb-lg">{{ now()->setDate($year, $month, 1)->locale(app()->getLocale())->translatedFormat('F Y') }}</p>
        <div class="flex-1 flex flex-col items-center justify-center relative" style="min-height: 200px;">
            @php
                $confirmedPct = $collectionPct ?? 0;
                $circumference = 2 * pi() * 70;
                $confirmedOffset = $circumference - ($circumference * min($confirmedPct, 100) / 100);
            @endphp
            <div class="relative" style="width: 192px; height: 192px;">
                <svg viewBox="0 0 192 192" class="w-full h-full transform -rotate-90">
                    <circle cx="96" cy="96" fill="transparent" r="70" stroke="currentColor" stroke-width="16" class="text-surface-container-low"></circle>
                    <circle cx="96" cy="96" fill="transparent" r="70" stroke="currentColor" stroke-width="16" stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $confirmedOffset }}" stroke-linecap="round" class="text-secondary transition-all duration-700"></circle>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="font-headline-lg text-headline-lg text-on-surface">{{ $confirmedPct }}%</span>
                    <span class="text-body-xs text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Cobrado' : 'Collected' }}</span>
                </div>
            </div>
        </div>
        <div class="space-y-md mt-lg">
            <div class="flex justify-between items-center text-body-md">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-secondary"></span>
                    <span>{{ app()->getLocale() === 'es' ? 'Cobrado' : 'Collected' }}</span>
                </div>
                <span class="font-mono-data font-bold">RD${{ number_format($totalCollected, 0) }}</span>
            </div>
            <div class="flex justify-between items-center text-body-md">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                    <span>{{ app()->getLocale() === 'es' ? 'Pendiente' : 'Pending' }}</span>
                </div>
                <span class="font-mono-data font-bold">RD${{ number_format($totalPending, 0) }}</span>
            </div>
            <div class="flex justify-between items-center text-body-md pt-sm border-t border-surface-container-low">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-primary"></span>
                    <span class="font-bold">{{ app()->getLocale() === 'es' ? 'Facturado' : 'Billed' }}</span>
                </div>
                <span class="font-mono-data font-bold">RD${{ number_format($totalBilled, 0) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Recent Movements Table --}}
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] overflow-hidden">
    <div class="p-lg flex justify-between items-center border-b border-surface-container-low">
        <h4 class="font-headline-md text-headline-md">{{ __('messages.dashboard.recent_movements') }}</h4>
        <a href="{{ route('payments.index') }}" class="text-primary font-bold text-body-md hover:underline">{{ __('messages.dashboard.view_all') }}</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-low/50">
                <tr>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.common.apartment') }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.common.resident') }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.common.concept') }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.common.amount') }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.common.date') }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.common.status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-container-low">
                @foreach ($recentMovements ?? [] as $movement)
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-lg py-md font-bold text-on-surface">{{ $movement['apartment'] }}</td>
                        <td class="px-lg py-md">{{ $movement['resident'] }}</td>
                        <td class="px-lg py-md text-on-surface-variant">{{ $movement['concept'] }}</td>
                        <td class="px-lg py-md font-mono-data">RD${{ number_format($movement['amount'], 2) }}</td>
                        <td class="px-lg py-md text-body-sm">{{ $movement['date'] }}</td>
                        <td class="px-lg py-md">
                            @switch($movement['status'])
                                @case('pending')
                                    <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold uppercase tracking-wider">{{ app()->getLocale() === 'es' ? 'Pendiente' : 'Pending' }}</span>
                                    @break
                                @case('confirmed')
                                    <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold uppercase tracking-wider">{{ app()->getLocale() === 'es' ? 'Confirmado' : 'Confirmed' }}</span>
                                    @break
                                @case('rejected')
                                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold uppercase tracking-wider">{{ app()->getLocale() === 'es' ? 'Rechazado' : 'Rejected' }}</span>
                                    @break
                                @default
                                    <span class="px-3 py-1 bg-surface-container-low text-on-surface-variant rounded-full text-xs font-bold uppercase tracking-wider">{{ $movement['status'] }}</span>
                            @endswitch
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection