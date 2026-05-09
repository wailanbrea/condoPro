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
<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-lg">
    {{-- Invoice Highlights --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
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
        <div class="space-y-sm">
            <span class="material-symbols-outlined text-primary text-3xl">insights</span>
            <h4 class="font-body-lg text-body-lg font-bold">{{ __('messages.resident.gas_usage') }}</h4>
            <p class="text-on-surface-variant text-body-sm">
                @if($gasReading)
                    {{ $gasReading->consumption_m3 }} m³ {{ app()->getLocale() === 'es' ? 'utilizados' : 'used' }}.
                @else
                    {{ app()->getLocale() === 'es' ? 'Sin datos de gas' : 'No gas data' }}
                @endif
            </p>
        </div>
        <div class="mt-lg">
            @php
                $gasPct = $gasReading ? min(($gasReading->consumption_m3 / 10) * 100, 100) : 0;
            @endphp
            <div class="w-full bg-surface-container-high h-2 rounded-full overflow-hidden">
                <div class="bg-secondary h-full rounded-full" style="width: {{ $gasPct }}%"></div>
            </div>
            <span class="text-xs text-on-surface-variant mt-sm block">
                {{ $gasReading ? number_format($gasReading->consumption_m3, 1) : '0' }} m³ {{ app()->getLocale() === 'es' ? 'utilizados' : 'used' }} {{ app()->getLocale() === 'es' ? 'de' : 'of' }} 10 m³ {{ app()->getLocale() === 'es' ? 'est.' : 'est.' }}
            </span>
        </div>
    </div>
    {{-- Building Notices Card --}}
    <div class="bg-white rounded-xl p-lg shadow-sm border border-outline-variant/30 flex flex-col justify-between">
        <div class="space-y-sm">
            <span class="material-symbols-outlined text-secondary text-3xl">notifications_active</span>
            <h4 class="font-body-lg text-body-lg font-bold">{{ __('messages.resident.building_notices') }}</h4>
            <p class="text-on-surface-variant text-body-sm">{{ app()->getLocale() === 'es' ? 'No hay avisos nuevos.' : 'No new notices.' }}</p>
        </div>
        <a class="text-primary font-bold text-body-sm flex items-center gap-xs mt-lg hover:underline" href="#">
            {{ app()->getLocale() === 'es' ? 'Ver cartelera' : 'View board' }} <span class="material-symbols-outlined text-sm">arrow_forward</span>
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