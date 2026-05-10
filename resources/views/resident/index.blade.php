@extends('layouts.resident')

@section('content')
{{-- Hero Section: Amount Owed --}}
<section class="grid grid-cols-1 lg:grid-cols-3 gap-lg">
    {{-- Debt Card --}}
    <div class="lg:col-span-2 rounded-xl p-lg shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center relative overflow-hidden {{ $totalOwed > 0 ? 'bg-red-600 text-white' : 'bg-green-600 text-white' }}">
        <div class="absolute right-0 top-0 opacity-10 pointer-events-none transform translate-x-1/4 -translate-y-1/4">
            <span class="material-symbols-outlined text-[160px]" style="font-variation-settings: 'FILL' 1;">{{ $totalOwed > 0 ? 'account_balance_wallet' : 'check_circle' }}</span>
        </div>
        <div class="z-10">
            <p class="font-label-caps text-label-caps opacity-90 uppercase tracking-widest mb-xs">{{ __('messages.resident.my_balance') }}</p>
            <h1 class="font-display-xl text-display-xl mb-sm">RD${{ number_format($totalOwed, 2) }}</h1>
            <div class="flex flex-col gap-xs text-body-sm opacity-80">
                <span class="flex items-center gap-xs">
                    <span class="material-symbols-outlined text-md">info</span>
                    {{ $totalOwed > 0 ? __('messages.resident.next_due') . ': ' . ($latestBill ? $latestBill->due_date->format('d M, Y') : '-') : (app()->getLocale() === 'es' ? 'Sin deuda pendiente' : 'No outstanding debt') }}
                </span>
                @if($pendingPayments > 0)
                <span class="flex items-center gap-xs">
                    <span class="material-symbols-outlined text-md">schedule</span>
                    {{ app()->getLocale() === 'es' ? 'Pagos pendientes de confirmar' : 'Payments pending confirmation' }}: RD${{ number_format($pendingPayments, 2) }}
                </span>
                @endif
                @if($totalPaid > 0)
                <span class="flex items-center gap-xs">
                    <span class="material-symbols-outlined text-md">check_circle</span>
                    {{ app()->getLocale() === 'es' ? 'Total pagado' : 'Total paid' }}: RD${{ number_format($totalPaid, 2) }}
                </span>
                @endif
            </div>
        </div>
        <div class="mt-lg md:mt-0 z-10 w-full md:w-auto">
            <a href="{{ route('resident.vouchers.upload') }}" class="w-full md:w-auto flex items-center justify-center gap-sm bg-white {{ $totalOwed > 0 ? 'text-red-600' : 'text-green-600' }} px-xl py-md rounded-lg font-bold shadow-lg hover:bg-surface-container-lowest transition-all">
                <span class="material-symbols-outlined">cloud_upload</span>
                {{ __('messages.resident.upload_voucher') }}
            </a>
        </div>
    </div>
    {{-- Profile Info Card --}}
    <div class="bg-white rounded-xl p-lg shadow-sm border border-outline-variant/30 flex items-center gap-lg">
        <div class="relative">
            <div class="w-16 h-16 rounded-full border-2 border-primary-container bg-primary/10 flex items-center justify-center text-primary font-bold text-xl">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="absolute bottom-0 right-0 w-4 h-4 bg-secondary border-2 border-white rounded-full"></div>
        </div>
        <div>
            <h3 class="font-headline-md text-headline-md text-on-surface">{{ $user->name }}</h3>
            <p class="text-on-surface-variant font-body-md">{{ $apartment ? $apartment->condominium->name . ', ' . __('messages.common.apartment') . ' ' . $apartment->number : '-' }}</p>
            <span class="inline-block mt-xs px-sm py-xs bg-secondary/10 text-secondary text-label-caps rounded uppercase font-bold">{{ __('messages.resident.active_resident') }}</span>
        </div>
    </div>
</section>

{{-- Fondo del Condominio --}}
<section class="mb-lg">
    <div class="bg-white rounded-xl p-lg shadow-sm border-l-4 {{ $condoFund >= 0 ? 'border-secondary' : 'border-error' }} flex items-center gap-lg">
        <div class="p-2 {{ $condoFund >= 0 ? 'bg-secondary/10 text-secondary' : 'bg-error/10 text-error' }} rounded-lg flex-shrink-0">
            <span class="material-symbols-outlined text-2xl" data-icon="account_balance">account_balance</span>
        </div>
        <div class="flex-1">
            <p class="text-on-surface-variant font-label-caps text-label-caps">{{ app()->getLocale() === 'es' ? 'Fondo del Condominio' : 'Condominium Fund' }}</p>
            <h3 class="font-headline-lg text-headline-lg {{ $condoFund >= 0 ? 'text-secondary' : 'text-error' }}">{{ $condoFund >= 0 ? '' : '-' }}RD${{ number_format(abs($condoFund), 0) }}</h3>
            <p class="text-body-xs text-on-surface-variant mt-xs">{{ app()->getLocale() === 'es' ? 'Acumulado histórico · Cobrado − Gastos + Ajustes' : 'All-time · Collected − Expenses + Adjustments' }}</p>
        </div>
        <span class="px-3 py-1 {{ $condoFund >= 0 ? 'bg-[#E3FCEF] text-[#006644]' : 'bg-[#FFEBE6] text-[#BF2600]' }} rounded-full text-xs font-bold uppercase tracking-wider flex-shrink-0">
            {{ $condoFund >= 0 ? (app()->getLocale() === 'es' ? 'Saldo Positivo' : 'Positive') : (app()->getLocale() === 'es' ? 'Déficit' : 'Deficit') }}
        </span>
    </div>
</section>

{{-- Current Invoice Detail & Info Grid --}}
<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-lg">
    {{-- Invoice Highlights --}}
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="px-lg py-md bg-surface-container-low border-b border-outline-variant/20 flex justify-between items-center">
            <h2 class="font-headline-md text-headline-md text-on-surface flex items-center gap-sm">
                <span class="material-symbols-outlined text-primary">description</span>
                {{ __('messages.resident.current_invoice') }}
            </h2>
            @if($latestBill)
                <span class="font-label-caps text-label-caps text-on-surface-variant">{{ strtoupper(\Carbon\Carbon::create($latestBill->billing_year, $latestBill->billing_month, 1)->format('F Y')) }}</span>
            @endif
        </div>
        <div class="p-lg space-y-md">
            @foreach ($billItems ?? [] as $item)
                <div class="flex justify-between items-center py-sm {{ !$loop->last ? 'border-b border-outline-variant/10' : '' }}">
                    <div class="flex items-center gap-md">
                        <span class="material-symbols-outlined text-on-surface-variant">{{ $item['icon'] }}</span>
                        <span class="font-body-md">{{ $item['concept'] }}</span>
                    </div>
                    <span class="font-mono-data text-mono-data">RD${{ number_format($item['amount'], 2) }}</span>
                </div>
            @endforeach

            @if($latestBill)
                <div class="pt-md">
                    <button class="w-full flex items-center justify-center gap-sm border-2 border-primary text-primary px-lg py-sm rounded-lg font-bold hover:bg-primary/5 transition-colors">
                        <span class="material-symbols-outlined">picture_as_pdf</span>
                        {{ __('messages.resident.view_pdf') }}
                    </button>
                </div>
            @endif
        </div>
    </div>
    {{-- Gas Consumption Card --}}
    <div class="bg-white rounded-xl p-lg shadow-sm border border-outline-variant/30 flex flex-col justify-between">
        <div class="space-y-md">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-sm">
                    <span class="material-symbols-outlined text-amber-600 text-3xl">local_gas_station</span>
                    <h4 class="font-body-lg text-body-lg font-bold">{{ app()->getLocale() === 'es' ? 'Consumo de Gas' : 'Gas Consumption' }}</h4>
                </div>
                @if($gasTrend === 'up')
                    <span class="px-2 py-1 bg-error/10 text-error text-xs font-bold rounded-full flex items-center gap-xs">
                        <span class="material-symbols-outlined text-sm">trending_up</span> {{ $gasTrendPercent }}%
                    </span>
                @elseif($gasTrend === 'down')
                    <span class="px-2 py-1 bg-secondary/10 text-secondary text-xs font-bold rounded-full flex items-center gap-xs">
                        <span class="material-symbols-outlined text-sm">trending_down</span> {{ abs($gasTrendPercent) }}%
                    </span>
                @else
                    <span class="px-2 py-1 bg-surface-container-high text-on-surface-variant text-xs font-bold rounded-full flex items-center gap-xs">
                        <span class="material-symbols-outlined text-sm">trending_flat</span> {{ app()->getLocale() === 'es' ? 'Estable' : 'Stable' }}
                    </span>
                @endif
            </div>
            
            {{-- Current month consumption --}}
            <div>
                @if($gasReading)
                    <div class="flex justify-between items-center mb-sm">
                        <span class="text-on-surface-variant text-body-sm">{{ app()->getLocale() === 'es' ? 'Mes actual:' : 'Current month:' }}</span>
                        <span class="font-mono-data font-bold text-on-surface">{{ number_format($gasReading->consumption_m3, 1) }} m³</span>
                    </div>
                    @php
                        $gasPct = min(($gasReading->consumption_m3 / 10) * 100, 100);
                    @endphp
                    <div class="w-full bg-surface-container-high h-2 rounded-full overflow-hidden">
                        <div class="bg-secondary h-full rounded-full" style="width: {{ $gasPct }}%"></div>
                    </div>
                    <span class="text-xs text-on-surface-variant mt-xs block">
                        {{ app()->getLocale() === 'es' ? 'De' : 'Of' }} 10 m³ {{ app()->getLocale() === 'es' ? 'estimados' : 'estimated' }}
                    </span>
                @else
                    <p class="text-on-surface-variant text-body-sm">{{ app()->getLocale() === 'es' ? 'Sin datos de gas' : 'No gas data' }}</p>
                @endif
            </div>
            
            {{-- History --}}
            <div class="pt-md border-t border-outline-variant/20">
                <p class="text-label-caps text-label-caps text-on-surface-variant mb-sm">{{ app()->getLocale() === 'es' ? 'Últimos meses' : 'Recent months' }}</p>
                <div class="space-y-xs">
                    @foreach($gasHistory->take(3) as $index => $gas)
                    <div class="flex justify-between items-center {{ $index > 0 ? 'text-on-surface-variant' : '' }}">
                        <span class="text-body-sm {{ $index === 0 ? 'font-bold text-on-surface' : '' }}">{{ ucfirst($gas['month_name']) }}</span>
                        <div class="text-right">
                            <span class="font-mono-data {{ $index === 0 ? 'font-bold text-on-surface' : '' }}">{{ number_format($gas['consumption'], 1) }} m³</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <a class="text-primary font-bold text-body-sm flex items-center gap-xs mt-lg hover:underline" href="{{ route('resident.history') }}">
            {{ app()->getLocale() === 'es' ? 'Ver historial' : 'View history' }} <span class="material-symbols-outlined text-sm">arrow_forward</span>
        </a>
    </div>

    {{-- Payment Summary Card --}}
    <div class="bg-white rounded-xl p-lg shadow-sm border border-outline-variant/30 flex flex-col justify-between">
        <div class="space-y-sm">
            <div class="flex items-center justify-between">
                <span class="material-symbols-outlined text-primary text-3xl">payments</span>
                @if($nextPayment)
                    @php
                        $daysUntil = now()->diffInDays($nextPayment->due_date, false);
                    @endphp
                    @if($daysUntil < 0)
                        <span class="px-2 py-1 bg-error/10 text-error text-xs font-bold rounded-full">{{ app()->getLocale() === 'es' ? 'Vencido' : 'Overdue' }}</span>
                    @elseif($daysUntil <= 7)
                        <span class="px-2 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full">{{ $daysUntil }} {{ app()->getLocale() === 'es' ? 'días' : 'days' }}</span>
                    @else
                        <span class="px-2 py-1 bg-secondary/10 text-secondary text-xs font-bold rounded-full">{{ app()->getLocale() === 'es' ? 'Al día' : 'Up to date' }}</span>
                    @endif
                @elseif($nextBillPreview)
                    <span class="px-2 py-1 bg-primary/10 text-primary text-xs font-bold rounded-full">{{ app()->getLocale() === 'es' ? 'Próxima' : 'Upcoming' }}</span>
                @else
                    <span class="px-2 py-1 bg-secondary/10 text-secondary text-xs font-bold rounded-full">{{ app()->getLocale() === 'es' ? 'Al día' : 'Up to date' }}</span>
                @endif
            </div>
            <h4 class="font-body-lg text-body-lg font-bold">{{ app()->getLocale() === 'es' ? 'Resumen de Pago' : 'Payment Summary' }}</h4>
            
            <div class="space-y-md">
                {{-- Next Payment / Expected Bill --}}
                @if($nextPayment)
                    {{-- Existing pending bill --}}
                    <div class="bg-surface-container-low rounded-lg p-md">
                        <p class="text-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Próximo pago' : 'Next payment' }}</p>
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-mono-data text-headline-md text-on-surface font-bold">RD${{ number_format($nextPayment->total - $nextPayment->payments_applied, 2) }}</p>
                                <p class="text-body-sm text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Vence:' : 'Due:' }} {{ $nextPayment->due_date->format('d M, Y') }}</p>
                            </div>
                        </div>
                    </div>
                @elseif($nextBillPreview)
                    {{-- Upcoming bill preview --}}
                    <div class="bg-surface-container-low rounded-lg p-md">
                        <p class="text-label-caps text-label-caps text-on-surface-variant mb-xs">
                            {{ app()->getLocale() === 'es' ? 'Próxima factura' : 'Next bill' }} · 
                            {{ $nextBillPreview['billing_date']->format('d M') }}
                        </p>
                        
                        <p class="font-mono-data text-headline-md text-on-surface font-bold mb-sm">RD${{ number_format($nextBillPreview['total'], 2) }}</p>
                        
                        {{-- Breakdown --}}
                        <div class="space-y-xs border-t border-outline-variant/20 pt-sm">
                            <div class="flex justify-between items-center">
                                <span class="text-body-sm text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Mantenimiento' : 'Maintenance' }}</span>
                                <span class="font-mono-data text-body-sm">RD${{ number_format($nextBillPreview['maintenance'], 0) }}</span>
                            </div>
                            @if($nextBillPreview['extra_charges'] > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-body-sm text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Cargos extra' : 'Extra charges' }}</span>
                                    <span class="font-mono-data text-body-sm">RD${{ number_format($nextBillPreview['extra_charges'], 0) }}</span>
                                </div>
                                @foreach($nextBillPreview['extra_details'] as $extra)
                                <div class="text-xs text-on-surface-variant pl-3 border-l-2 border-outline-variant/30">
                                    {{ $extra['title'] }}: RD${{ number_format($extra['amount'], 0) }}
                                </div>
                                @endforeach
                            @endif
                        </div>
                        
                        <p class="text-xs text-primary mt-sm">
                            <span class="material-symbols-outlined text-xs align-middle">event</span>
                            {{ app()->getLocale() === 'es' ? 'Se genera el día' : 'Generated on day' }} {{ $nextBillPreview['billing_date']->day }} 
                            ({{ $nextBillPreview['days_until'] }} {{ app()->getLocale() === 'es' ? 'días' : 'days' }})
                        </p>
                    </div>
                @else
                    <div class="bg-surface-container-low rounded-lg p-md">
                        <p class="text-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Estado' : 'Status' }}</p>
                        <p class="text-on-surface-variant text-body-sm">{{ app()->getLocale() === 'es' ? 'Factura ya generada para este período' : 'Bill already generated for this period' }}</p>
                    </div>
                @endif
                
                {{-- Quick stats --}}
                <div class="grid grid-cols-2 gap-sm">
                    <div>
                        <p class="text-xs text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Deuda total' : 'Total debt' }}</p>
                        <p class="font-mono-data font-bold {{ $financialSummary['total_owed'] > 0 ? 'text-error' : 'text-secondary' }}">RD${{ number_format($financialSummary['total_owed'], 0) }}</p>
                    </div>
                    @if($financialSummary['last_payment'])
                    <div>
                        <p class="text-xs text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Último pago' : 'Last payment' }}</p>
                        <p class="font-mono-data text-secondary">RD${{ number_format($financialSummary['last_payment'], 0) }}</p>
                        <p class="text-xs text-on-surface-variant">{{ $financialSummary['last_payment_date']?->format('d M') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <a class="text-primary font-bold text-body-sm flex items-center gap-xs mt-lg hover:underline" href="{{ route('resident.invoices') }}">
            {{ app()->getLocale() === 'es' ? 'Ver facturas' : 'View invoices' }} <span class="material-symbols-outlined text-sm">arrow_forward</span>
        </a>
    </div>
</section>

{{-- History Section --}}
<section class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
    <div class="px-lg py-md border-b border-outline-variant/20 flex justify-between items-center bg-surface-container-lowest">
        <h2 class="font-headline-md text-headline-md text-on-surface flex items-center gap-sm">
            <span class="material-symbols-outlined text-primary">history</span>
            {{ __('messages.resident.recent_history') }}
        </h2>
        <button class="text-primary font-bold text-body-sm hover:underline">{{ app()->getLocale() === 'es' ? 'Ver Todo' : 'View All' }}</button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-surface-container-low/50">
                    <th class="px-lg py-md font-label-caps text-label-caps text-on-surface-variant">{{ __('messages.common.date') }}</th>
                    <th class="px-lg py-md font-label-caps text-label-caps text-on-surface-variant">{{ __('messages.common.concept') }}</th>
                    <th class="px-lg py-md font-label-caps text-label-caps text-on-surface-variant">{{ __('messages.common.amount') }}</th>
                    <th class="px-lg py-md font-label-caps text-label-caps text-on-surface-variant">{{ __('messages.common.status') }}</th>
                    <th class="px-lg py-md font-label-caps text-label-caps text-on-surface-variant">{{ __('messages.common.reference') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/10">
                @foreach ($paymentHistory ?? [] as $payment)
                    <tr class="hover:bg-surface-container-low transition-colors group">
                        <td class="px-lg py-md font-body-md text-on-surface">{{ $payment['date'] }}</td>
                        <td class="px-lg py-md">
                            <div class="flex flex-col">
                                <span class="font-body-md font-bold">{{ $payment['concept'] }}</span>
                                <span class="text-xs text-on-surface-variant">{{ __('messages.common.reference') }}: #{{ $payment['reference'] }}</span>
                            </div>
                        </td>
                        <td class="px-lg py-md font-mono-data text-on-surface">RD${{ number_format($payment['amount'], 2) }}</td>
                        <td class="px-lg py-md">
                            @switch($payment['status'])
                                @case('confirmed')
                                    <span class="inline-flex items-center gap-xs px-sm py-xs bg-secondary/10 text-secondary text-body-sm rounded-full font-semibold">
                                        <span class="w-2 h-2 bg-secondary rounded-full"></span>
                                        {{ __('messages.common.confirmed') }}
                                    </span>
                                    @break
                                @case('pending')
                                    <span class="inline-flex items-center gap-xs px-sm py-xs bg-tertiary-fixed text-tertiary text-body-sm rounded-full font-semibold">
                                        <span class="w-2 h-2 bg-tertiary rounded-full"></span>
                                        {{ __('messages.common.pending') }}
                                    </span>
                                    @break
                                @case('paid')
                                    <span class="inline-flex items-center gap-xs px-sm py-xs bg-secondary/10 text-secondary text-body-sm rounded-full font-semibold">
                                        <span class="w-2 h-2 bg-secondary rounded-full"></span>
                                        {{ __('messages.common.paid') }}
                                    </span>
                                    @break
                                @case('overdue')
                                    <span class="inline-flex items-center gap-xs px-sm py-xs bg-[#FFEBE6] text-[#BF2600] text-body-sm rounded-full font-semibold">
                                        <span class="w-2 h-2 bg-[#BF2600] rounded-full"></span>
                                        {{ __('messages.common.overdue') }}
                                    </span>
                                    @break
                                @default
                                    <span class="inline-flex items-center gap-xs px-sm py-xs bg-surface-container-low text-on-surface-variant text-body-sm rounded-full font-semibold">
                                        {{ $payment['status'] }}
                                    </span>
                            @endswitch
                        </td>
                        <td class="px-lg py-md">
                            @if($payment['status'] === 'confirmed' || $payment['status'] === 'paid')
                                <button class="text-on-surface-variant hover:text-primary"><span class="material-symbols-outlined">receipt</span></button>
                            @else
                                <button class="text-on-surface-variant hover:text-primary"><span class="material-symbols-outlined">visibility</span></button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection