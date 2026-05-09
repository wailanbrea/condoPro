@extends('layouts.resident')

@section('content')
<section class="space-y-lg">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-md">
        <div class="flex items-center gap-sm">
            <span class="material-symbols-outlined text-primary text-3xl">description</span>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">{{ __('messages.resident.my_invoices') }}</h1>
        </div>
        <div class="flex items-center gap-sm">
            <label for="year_filter" class="font-label-caps text-label-caps text-on-surface-variant">{{ __('messages.resident.filter_year') }}:</label>
            <select id="year_filter" class="px-md py-sm border border-outline-variant/50 rounded-lg bg-white text-on-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                <option value="">{{ __('messages.resident.all_years') }}</option>
                @foreach ($years as $year)
                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Invoices Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        @if ($bills->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low/50">
                            <th class="px-lg py-md font-label-caps text-label-caps text-on-surface-variant">{{ __('messages.resident.period') }}</th>
                            <th class="px-lg py-md font-label-caps text-label-caps text-on-surface-variant">{{ __('messages.resident.concepts') }}</th>
                            <th class="px-lg py-md font-label-caps text-label-caps text-on-surface-variant">{{ __('messages.common.total') }}</th>
                            <th class="px-lg py-md font-label-caps text-label-caps text-on-surface-variant">{{ __('messages.common.status') }}</th>
                            <th class="px-lg py-md font-label-caps text-label-caps text-on-surface-variant">{{ __('messages.common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        @foreach ($bills as $bill)
                            <tr class="hover:bg-surface-container-low transition-colors">
                                <td class="px-lg py-md font-body-md font-bold text-on-surface whitespace-nowrap">
                                    {{ \Carbon\Carbon::create($bill->billing_year, $bill->billing_month, 1)->format('M Y') }}
                                </td>
                                <td class="px-lg py-md">
                                    <div class="flex flex-col">
                                        @foreach ($bill->billItems->take(2) as $item)
                                            <span class="font-body-md text-on-surface">{{ $item->description }}</span>
                                        @endforeach
                                        @if ($bill->billItems->count() > 2)
                                            <span class="text-xs text-on-surface-variant">+{{ $bill->billItems->count() - 2 }} {{ app()->getLocale() === 'es' ? 'más' : 'more' }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-lg py-md font-mono-data text-on-surface">RD${{ number_format($bill->total, 2) }}</td>
                                <td class="px-lg py-md">
                                    @switch($bill->status)
                                        @case('pending')
                                            <span class="inline-flex items-center gap-xs px-sm py-xs bg-tertiary-fixed text-tertiary text-body-sm rounded-full font-semibold">
                                                <span class="w-2 h-2 bg-tertiary rounded-full"></span>
                                                {{ __('messages.common.pending') }}
                                            </span>
                                            @break
                                        @case('partial')
                                            <span class="inline-flex items-center gap-xs px-sm py-xs bg-orange-100 text-orange-700 text-body-sm rounded-full font-semibold">
                                                <span class="w-2 h-2 bg-orange-500 rounded-full"></span>
                                                {{ __('messages.resident.partial') }}
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
                                                {{ $bill->status }}
                                            </span>
                                    @endswitch
                                </td>
                                <td class="px-lg py-md">
                                    <a href="{{ route('resident.invoices.show', $bill) }}" class="text-on-surface-variant hover:text-primary transition-colors flex items-center gap-xs font-body-md">
                                        <span class="material-symbols-outlined">visibility</span>
                                        {{ __('messages.resident.view_detail') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-xl text-center space-y-sm">
                <span class="material-symbols-outlined text-5xl text-on-surface-variant/40">description</span>
                <p class="font-body-lg text-on-surface-variant">{{ __('messages.resident.no_invoices') }}</p>
            </div>
        @endif
    </div>
</section>

@push('scripts')
<script>
    document.getElementById('year_filter').addEventListener('change', function () {
        const url = new URL(window.location.href);
        if (this.value) {
            url.searchParams.set('year', this.value);
        } else {
            url.searchParams.delete('year');
        }
        window.location.href = url.toString();
    });
</script>
@endpush
@endsection