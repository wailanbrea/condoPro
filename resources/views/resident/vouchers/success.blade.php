@extends('layouts.resident')

@section('content')
<section class="flex items-center justify-center min-h-[60vh]">
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-xl text-center max-w-md w-full space-y-lg">
        <div class="flex justify-center">
            <div class="w-20 h-20 rounded-full bg-secondary/10 flex items-center justify-center">
                <span class="material-symbols-outlined text-secondary text-5xl" style="font-variation-settings: 'FILL' 1;">check_circle</span>
            </div>
        </div>

        <h1 class="font-headline-lg text-headline-lg text-on-surface">{{ __('messages.resident.voucher_success') }}</h1>

        <p class="font-body-md text-on-surface-variant">{{ __('messages.resident.voucher_pending_confirmation') }}</p>

        <div class="flex flex-col sm:flex-row gap-md justify-center pt-sm">
            <a href="{{ route('resident.vouchers.upload') }}"
                class="flex items-center justify-center gap-sm bg-primary text-on-primary px-xl py-md rounded-lg font-bold shadow-sm hover:bg-primary-container hover:text-on-primary-container transition-colors">
                <span class="material-symbols-outlined">cloud_upload</span>
                {{ __('messages.resident.upload_another') }}
            </a>
            <a href="{{ route('resident.history') }}"
                class="flex items-center justify-center gap-sm border-2 border-primary text-primary px-xl py-md rounded-lg font-bold hover:bg-primary/5 transition-colors">
                <span class="material-symbols-outlined">history</span>
                {{ __('messages.resident.view_history') }}
            </a>
        </div>
    </div>
</section>
@endsection