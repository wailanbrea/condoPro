@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">Informe Financiero</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">Informe Financiero Mensual</h2>
        <p class="text-on-surface-variant">Estado de ingresos, egresos y balance general</p>
    </div>
    <button type="button" id="generateReportBtn" class="px-lg py-2 bg-primary text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all">
        <span class="material-symbols-outlined" data-icon="summarize">summarize</span>
        Generar Informe
    </button>
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

{{-- Filter Bar --}}
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg mb-lg">
    <form method="GET" action="{{ route('financial-reports.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-md items-end">
        <div>
            <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1">Condominio</label>
            <select name="condominium_id" class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface">
                @foreach($condominiums as $condo)
                    <option value="{{ $condo->id }}" {{ $selectedCondo && $selectedCondo->id === $condo->id ? 'selected' : '' }}>{{ $condo->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1">Mes</label>
            <select name="month" class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ ucfirst(\Carbon\Carbon::create()->month($m)->monthName) }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1">Año</label>
            <select name="year" class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface">
                @for($y = now()->year - 3; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div>
            <button type="submit" class="w-full px-lg py-2 bg-primary text-white rounded-lg flex items-center justify-center gap-2 hover:brightness-110 transition-all">
                <span class="material-symbols-outlined" data-icon="filter_list">filter_list</span>
                Consultar
            </button>
        </div>
        <div>
            <button type="button" id="addMovementBtn" class="w-full px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center justify-center gap-2 hover:bg-surface-container-low transition-colors">
                <span class="material-symbols-outlined" data-icon="add_circle">add_circle</span>
                Movimiento
            </button>
        </div>
    </form>
</div>

{{-- ============================================ --}}
{{-- SECTION 1: INGRESOS DEL MES --}}
{{-- ============================================ --}}
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] overflow-hidden mb-lg">
    <div class="px-lg py-md bg-[#1B5E20] text-white flex items-center gap-2">
        <span class="material-symbols-outlined" data-icon="trending_up">trending_up</span>
        <h3 class="font-headline-md">INGRESOS DEL MES</h3>
        <span class="ml-auto font-mono-data text-headline-md">RD${{ number_format($totalIncome, 2) }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#E8F5E9] border-b-2 border-[#1B5E20]">
                    <th class="px-md py-md text-label-caps font-label-caps text-[#1B5E20] whitespace-nowrap">APTO</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-[#1B5E20] whitespace-nowrap">NOMBRE</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-[#1B5E20] whitespace-nowrap text-right">MANTENIMIENTO</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-[#1B5E20] whitespace-nowrap text-right">GAS</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-[#1B5E20] whitespace-nowrap text-right">ATRASO / CUOTA EXTRA</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-[#1B5E20] whitespace-nowrap text-right">TOTAL</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-amber-800 whitespace-nowrap text-right">PENDIENTE</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @foreach($incomeRows as $row)
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-md py-md font-bold text-on-surface">{{ $row['apartment']->number }}</td>
                        <td class="px-md py-md text-on-surface-variant">{{ $row['apartment']->owner_name }}</td>
                        <td class="px-md py-md font-mono-data text-on-surface text-right">{{ number_format($row['maintenance'], 2) }}</td>
                        <td class="px-md py-md font-mono-data text-on-surface text-right">{{ number_format($row['gas'], 2) }}</td>
                        <td class="px-md py-md font-mono-data text-on-surface text-right">{{ number_format($row['extra_charges'], 2) }}</td>
                        <td class="px-md py-md font-mono-data text-on-surface text-right font-bold">{{ number_format($row['total'], 2) }}</td>
                        <td class="px-md py-md font-mono-data {{ $row['pending'] > 0 ? 'text-amber-700 font-bold' : 'text-on-surface-variant' }} text-right">
                            @if($row['pending'] > 0)
                                {{ number_format($row['pending'], 2) }}
                            @else
                                <span class="text-[#006644]">0.00</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-[#E8F5E9] border-t-2 border-[#1B5E20]">
                    <td class="px-md py-md font-label-caps font-bold text-[#1B5E20]" colspan="2">TOTALES</td>
                    <td class="px-md py-md font-mono-data text-[#1B5E20] text-right font-bold">{{ number_format($totalMaintenance, 2) }}</td>
                    <td class="px-md py-md font-mono-data text-[#1B5E20] text-right font-bold">{{ number_format($totalGas, 2) }}</td>
                    <td class="px-md py-md font-mono-data text-[#1B5E20] text-right font-bold">{{ number_format($totalExtraCharges, 2) }}</td>
                    <td class="px-md py-md font-mono-data text-[#1B5E20] text-right font-bold text-lg">{{ number_format($totalIncome, 2) }}</td>
                    <td class="px-md py-md font-mono-data text-amber-700 text-right font-bold">{{ number_format($totalPending, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- ============================================ --}}
{{-- SECTION 2: EGRESOS DEL MES --}}
{{-- ============================================ --}}
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] overflow-hidden mb-lg">
    <div class="px-lg py-md bg-[#B71C1C] text-white flex items-center gap-2">
        <span class="material-symbols-outlined" data-icon="trending_down">trending_down</span>
        <h3 class="font-headline-md">EGRESOS DEL MES</h3>
        <span class="ml-auto font-mono-data text-headline-md">RD${{ number_format($totalExpenses, 2) }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#FFEBEE] border-b-2 border-[#B71C1C]">
                    <th class="px-md py-md text-label-caps font-label-caps text-[#B71C1C] whitespace-nowrap">CONCEPTO</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-[#B71C1C] whitespace-nowrap">CATEGORÍA</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-[#B71C1C] whitespace-nowrap">FECHA</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-[#B71C1C] whitespace-nowrap text-right">VALOR</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @forelse($expenses as $expense)
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-md py-md text-on-surface">{{ $expense->concept }}</td>
                        <td class="px-md py-md text-on-surface-variant">{{ $expense->category?->name ?? '—' }}</td>
                        <td class="px-md py-md text-on-surface-variant">{{ $expense->date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-md py-md font-mono-data text-on-surface text-right font-bold">{{ number_format($expense->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-lg py-xl text-center text-on-surface-variant">No hay egresos registrados para este período</td>
                    </tr>
                @endforelse
            </tbody>
            @if($expenses->count() > 0)
                <tfoot>
                    <tr class="bg-[#FFEBEE] border-t-2 border-[#B71C1C]">
                        <td class="px-md py-md font-label-caps font-bold text-[#B71C1C]" colspan="3">TOTAL GASTOS</td>
                        <td class="px-md py-md font-mono-data text-[#B71C1C] text-right font-bold text-lg">{{ number_format($totalExpenses, 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- ============================================ --}}
{{-- SECTION 3: MOVIMIENTOS ADICIONALES --}}
{{-- ============================================ --}}
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] overflow-hidden mb-lg">
    <div class="px-lg py-md bg-[#4A148C] text-white flex items-center gap-2">
        <span class="material-symbols-outlined" data-icon="swap_horiz">swap_horiz</span>
        <h3 class="font-headline-md">MOVIMIENTOS ADICIONALES</h3>
        @if($totalMovementIncome > 0)
            <span class="ml-2 px-2 py-0.5 bg-[#E8F5E9] text-[#1B5E20] rounded text-xs font-bold">+RD${{ number_format($totalMovementIncome, 2) }}</span>
        @endif
        @if($totalMovementExpense > 0)
            <span class="ml-2 px-2 py-0.5 bg-[#FFEBEE] text-[#B71C1C] rounded text-xs font-bold">-RD${{ number_format($totalMovementExpense, 2) }}</span>
        @endif
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#F3E5F5] border-b-2 border-[#4A148C]">
                    <th class="px-md py-md text-label-caps font-label-caps text-[#4A148C] whitespace-nowrap">TIPO</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-[#4A148C] whitespace-nowrap">CATEGORÍA</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-[#4A148C] whitespace-nowrap">DESCRIPCIÓN</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-[#4A148C] whitespace-nowrap">FECHA</th>
                    <th class="px-md py-md text-label-caps font-label-caps text-[#4A148C] whitespace-nowrap text-right">MONTO</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @forelse($movements as $movement)
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-md py-md">
                            @switch($movement->movement_type)
                                @case('income')
                                    <span class="px-2 py-1 bg-[#E8F5E9] text-[#1B5E20] rounded-full text-[11px] font-bold uppercase tracking-wider">Ingreso</span>
                                    @break
                                @case('expense')
                                    <span class="px-2 py-1 bg-[#FFEBEE] text-[#B71C1C] rounded-full text-[11px] font-bold uppercase tracking-wider">Egreso</span>
                                    @break
                                @case('adjustment')
                                    <span class="px-2 py-1 bg-[#F3E5F5] text-[#4A148C] rounded-full text-[11px] font-bold uppercase tracking-wider">Ajuste</span>
                                    @break
                                @default
                                    <span class="px-2 py-1 bg-surface-container-low text-on-surface-variant rounded-full text-[11px] font-bold uppercase tracking-wider">{{ $movement->movement_type }}</span>
                            @endswitch
                        </td>
                        <td class="px-md py-md text-on-surface">{{ $movement->category }}</td>
                        <td class="px-md py-md text-on-surface-variant">{{ $movement->description ?? '—' }}</td>
                        <td class="px-md py-md text-on-surface-variant">{{ $movement->movement_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-md py-md font-mono-data text-right font-bold {{ $movement->movement_type === 'income' ? 'text-[#1B5E20]' : ($movement->movement_type === 'expense' ? 'text-[#B71C1C]' : 'text-[#4A148C]') }}">
                            {{ $movement->movement_type === 'income' ? '+' : '-' }}{{ number_format($movement->amount, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-lg py-xl text-center text-on-surface-variant">No hay movimientos adicionales registrados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ============================================ --}}
{{-- SECTION 3: RESUMEN FINANCIERO --}}
{{-- ============================================ --}}
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] overflow-hidden mb-lg border-l-4 border-primary">
    <div class="px-lg py-md bg-primary text-white flex items-center gap-2">
        <span class="material-symbols-outlined" data-icon="account_balance">account_balance</span>
        <h3 class="font-headline-md">RESUMEN FINANCIERO</h3>
    </div>
    <div class="p-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-lg">
            <div class="bg-surface-container-low rounded-lg p-md border border-outline-variant">
                <p class="font-label-caps text-label-caps text-on-surface-variant mb-xs">Balance Inicial en Banco</p>
                <p class="font-mono-data text-headline-lg text-on-surface font-bold">{{ number_format($initialBalance, 2) }}</p>
            </div>
            <div class="bg-[#E8F5E9] rounded-lg p-md border border-[#1B5E20]/20">
                <p class="font-label-caps text-label-caps text-[#1B5E20] mb-xs">+ Ingresos</p>
                <p class="font-mono-data text-headline-lg text-[#1B5E20] font-bold">RD${{ number_format($totalIncome + $totalMovementIncome, 2) }}</p>
            </div>
            <div class="bg-[#FFEBEE] rounded-lg p-md border border-[#B71C1C]/20">
                <p class="font-label-caps text-label-caps text-[#B71C1C] mb-xs">− Gastos</p>
                <p class="font-mono-data text-headline-lg text-[#B71C1C] font-bold">RD${{ number_format($totalExpenses + $totalMovementExpense, 2) }}</p>
            </div>
            <div class="bg-[#FFF3E0] rounded-lg p-md border border-amber-500/20">
                <p class="font-label-caps text-label-caps text-amber-800 mb-xs">− Pagos Especiales</p>
                <p class="font-mono-data text-headline-lg text-amber-800 font-bold">RD${{ number_format($specialPayments, 2) }}</p>
            </div>
            @if($totalAdjustments != 0)
                <div class="bg-[#F3E5F5] rounded-lg p-md border border-[#4A148C]/20">
                    <p class="font-label-caps text-label-caps text-[#4A148C] mb-xs">Ajustes</p>
                    <p class="font-mono-data text-headline-lg text-[#4A148C] font-bold">{{ $totalAdjustments > 0 ? '+' : '' }}RD${{ number_format($totalAdjustments, 2) }}</p>
                </div>
            @endif
            <div class="bg-primary rounded-lg p-md border-2 border-primary">
                <p class="font-label-caps text-white/80 mb-xs">BALANCE FINAL</p>
                <p class="font-mono-data text-headline-lg text-white font-bold">RD${{ number_format($finalBalance, 2) }}</p>
            </div>
        </div>

        <div class="mt-lg p-md bg-surface-container-low rounded-lg">
            <p class="font-bold text-on-surface mb-sm">Fórmula:</p>
            <p class="font-mono-data text-on-surface-variant text-body-sm">
                Balance Final = Balance Inicial + Ingresos − Gastos − Pagos Especiales + Ajustes
            </p>
            <p class="font-mono-data text-on-surface-variant text-body-sm mt-xs">
                RD${{ number_format($initialBalance, 2) }} + RD${{ number_format($totalIncome + $totalMovementIncome, 2) }} − RD${{ number_format($totalExpenses + $totalMovementExpense, 2) }} − RD${{ number_format($specialPayments, 2) }} + RD${{ number_format($totalAdjustments, 2) }} = <strong class="text-primary">RD${{ number_format($finalBalance, 2) }}</strong>
            </p>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- MODAL: Generate Financial Report --}}
{{-- ============================================ --}}
<div id="generateReportModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-lg">
        <div class="flex justify-between items-center mb-lg">
            <h3 class="font-headline-md text-headline-md text-on-surface">Generar Informe Financiero</h3>
            <button type="button" id="closeGenerateModal" class="p-1 hover:bg-surface-container-high rounded-lg text-on-surface-variant">
                <span class="material-symbols-outlined" data-icon="close">close</span>
            </button>
        </div>
        <form action="{{ route('financial-reports.store') }}" method="POST" id="generateForm">
            @csrf
            <input type="hidden" name="condominium_id" value="{{ $selectedCondo?->id ?? '' }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="year" value="{{ $year }}">
            <div class="space-y-lg">
                <div>
                    <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="initial_balance">Balance Inicial en Banco (RD$)</label>
                    <input type="number" id="initial_balance" name="initial_balance" value="{{ number_format($initialBalance, 2, '.', '') }}" step="0.01"
                        class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface text-right font-mono-data" />
                </div>
                <div>
                    <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="special_payments">Pagos Especiales (RD$)</label>
                    <input type="number" id="special_payments" name="special_payments" value="0" step="0.01"
                        class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface text-right font-mono-data" />
                </div>
                <div>
                    <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="notes">Notas</label>
                    <textarea id="notes" name="notes" rows="3"
                        class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface"
                        placeholder="Observaciones adicionales sobre el informe..."></textarea>
                </div>
                <div class="p-md bg-surface-container-low rounded-lg">
                    <p class="text-body-sm text-on-surface-variant"><strong>Período:</strong> {{ ucfirst(\Carbon\Carbon::create()->month($month)->monthName) }} {{ $year }}</p>
                    @if($selectedCondo)
                        <p class="text-body-sm text-on-surface-variant"><strong>Condominio:</strong> {{ $selectedCondo->name }}</p>
                    @endif
                    @if($existingReport)
                        <p class="text-body-sm text-[#BF2600]"><strong>Ya existe un informe para este período.</strong></p>
                    @endif
                </div>
            </div>
            <div class="flex gap-md mt-xl">
                <button type="submit" class="px-lg py-2 bg-primary text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all" {{ $existingReport ? 'disabled' : '' }}>
                    <span class="material-symbols-outlined" data-icon="summarize">summarize</span>
                    Generar Informe
                </button>
                <button type="button" id="cancelGenerate" class="px-lg py-2 bg-white border border-outline-variant rounded-lg hover:bg-surface-container-low transition-colors">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ============================================ --}}
{{-- MODAL: Add Movement --}}
{{-- ============================================ --}}
<div id="addMovementModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-lg">
        <div class="flex justify-between items-center mb-lg">
            <h3 class="font-headline-md text-headline-md text-on-surface">Registrar Movimiento</h3>
            <button type="button" id="closeMovementModal" class="p-1 hover:bg-surface-container-high rounded-lg text-on-surface-variant">
                <span class="material-symbols-outlined" data-icon="close">close</span>
            </button>
        </div>
        <form action="{{ route('financial-movements.store') }}" method="POST">
            @csrf
            <input type="hidden" name="condominium_id" value="{{ $selectedCondo?->id ?? '' }}">
            <div class="space-y-lg">
                <div>
                    <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="movement_type">Tipo de Movimiento <span class="text-error">*</span></label>
                    <select id="movement_type" name="movement_type" required class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface">
                        <option value="income">Ingreso</option>
                        <option value="expense">Egreso</option>
                        <option value="adjustment">Ajuste</option>
                    </select>
                </div>
                <div>
                    <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="category">Categoría <span class="text-error">*</span></label>
                    <select id="category" name="category" required class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface">
                    </select>
                </div>
                <div>
                    <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="amount">Monto (RD$) <span class="text-error">*</span></label>
                    <input type="number" id="amount" name="amount" step="0.01" min="0.01" required
                        class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface text-right font-mono-data" placeholder="0.00" />
                </div>
                <div>
                    <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="movement_date">Fecha <span class="text-error">*</span></label>
                    <input type="date" id="movement_date" name="movement_date" required value="{{ now()->format('Y-m-d') }}"
                        class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface" />
                </div>
                <div>
                    <label class="block font-label-caps text-label-caps text-on-surface-variant mb-1" for="description">Descripción</label>
                    <textarea id="description" name="description" rows="2"
                        class="w-full px-md py-2 border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface"
                        placeholder="Descripción del movimiento..."></textarea>
                </div>
            </div>
            <div class="flex gap-md mt-xl">
                <button type="submit" class="px-lg py-2 bg-primary text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all">
                    <span class="material-symbols-outlined" data-icon="save">save</span>
                    Guardar Movimiento
                </button>
                <button type="button" id="cancelMovement" class="px-lg py-2 bg-white border border-outline-variant rounded-lg hover:bg-surface-container-low transition-colors">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const generateBtn = document.getElementById('generateReportBtn');
    const generateModal = document.getElementById('generateReportModal');
    const closeGenerateModal = document.getElementById('closeGenerateModal');
    const cancelGenerate = document.getElementById('cancelGenerate');

    const addMovementBtn = document.getElementById('addMovementBtn');
    const addMovementModal = document.getElementById('addMovementModal');
    const closeMovementModal = document.getElementById('closeMovementModal');
    const cancelMovement = document.getElementById('cancelMovement');

    const movementType = document.getElementById('movement_type');
    const categorySelect = document.getElementById('category');

    const categoryOptions = {{ json_encode(\App\Models\FinancialMovement::getCategoryOptions()) }};

    function updateCategories() {
        const type = movementType.value;
        const options = categoryOptions[type] || {};
        categorySelect.innerHTML = '';
        Object.entries(options).forEach(function([value, label]) {
            const opt = document.createElement('option');
            opt.value = value;
            opt.textContent = label;
            categorySelect.appendChild(opt);
        });
    }

    if (generateBtn) {
        generateBtn.addEventListener('click', function() { generateModal.classList.remove('hidden'); });
        closeGenerateModal.addEventListener('click', function() { generateModal.classList.add('hidden'); });
        cancelGenerate.addEventListener('click', function() { generateModal.classList.add('hidden'); });
    }

    if (addMovementBtn) {
        addMovementBtn.addEventListener('click', function() { addMovementModal.classList.remove('hidden'); });
        closeMovementModal.addEventListener('click', function() { addMovementModal.classList.add('hidden'); });
        cancelMovement.addEventListener('click', function() { addMovementModal.classList.add('hidden'); });
    }

    movementType.addEventListener('change', updateCategories);
    updateCategories();
})();
</script>
@endpush
@endsection