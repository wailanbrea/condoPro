<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'CondoPro') }} - {{ __('messages.nav.resident_portal') }}</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface-tint": "#0c56d0",
                        "on-secondary": "#ffffff",
                        "secondary-fixed-dim": "#65dca4",
                        "on-tertiary-fixed": "#291800",
                        "on-tertiary-container": "#ffca81",
                        "error-container": "#ffdad6",
                        "primary-fixed": "#dae2ff",
                        "surface-container-high": "#dfe8ff",
                        "inverse-surface": "#20314b",
                        "inverse-on-surface": "#ecf0ff",
                        "on-tertiary-fixed-variant": "#624000",
                        "surface-bright": "#f9f9ff",
                        "surface-dim": "#cadbfc",
                        "on-primary-fixed-variant": "#0040a2",
                        "secondary": "#006c47",
                        "on-secondary-container": "#00734c",
                        "on-error-container": "#93000a",
                        "outline-variant": "#c3c6d6",
                        "on-tertiary": "#ffffff",
                        "background": "#f9f9ff",
                        "secondary-fixed": "#82f9be",
                        "on-surface": "#091c35",
                        "on-primary": "#ffffff",
                        "on-secondary-fixed": "#002113",
                        "surface": "#f9f9ff",
                        "primary": "#003d9b",
                        "surface-container-low": "#f0f3ff",
                        "surface-container-lowest": "#ffffff",
                        "secondary-container": "#82f9be",
                        "on-surface-variant": "#434654",
                        "primary-fixed-dim": "#b2c5ff",
                        "outline": "#737685",
                        "tertiary-container": "#7d5200",
                        "surface-variant": "#d6e3ff",
                        "on-primary-fixed": "#001848",
                        "on-primary-container": "#c4d2ff",
                        "surface-container": "#e7eeff",
                        "error": "#ba1a1a",
                        "on-secondary-fixed-variant": "#005235",
                        "tertiary-fixed": "#ffddb3",
                        "inverse-primary": "#b2c5ff",
                        "tertiary-fixed-dim": "#ffb950",
                        "surface-container-highest": "#d6e3ff",
                        "tertiary": "#5e3c00",
                        "primary-container": "#0052cc",
                        "on-background": "#091c35",
                        "on-error": "#ffffff"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "sm": "8px",
                        "lg": "24px",
                        "gutter": "24px",
                        "base": "4px",
                        "xl": "32px",
                        "md": "16px",
                        "xs": "4px",
                        "margin": "40px"
                    },
                    "fontFamily": {
                        "body-lg": ["Inter"],
                        "body-sm": ["Inter"],
                        "body-md": ["Inter"],
                        "headline-md": ["Inter"],
                        "display-xl": ["Inter"],
                        "mono-data": ["JetBrains Mono"],
                        "label-caps": ["Inter"],
                        "headline-lg": ["Inter"]
                    },
                    "fontSize": {
                        "body-lg": ["16px", {"lineHeight": "24px", "letterSpacing": "0", "fontWeight": "400"}],
                        "body-sm": ["13px", {"lineHeight": "18px", "letterSpacing": "0", "fontWeight": "400"}],
                        "body-md": ["14px", {"lineHeight": "20px", "letterSpacing": "0", "fontWeight": "400"}],
                        "headline-md": ["20px", {"lineHeight": "28px", "letterSpacing": "-0.01em", "fontWeight": "600"}],
                        "display-xl": ["36px", {"lineHeight": "44px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "mono-data": ["14px", {"lineHeight": "20px", "letterSpacing": "-0.01em", "fontWeight": "500"}],
                        "label-caps": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "700"}],
                        "headline-lg": ["24px", {"lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600"}]
                    }
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        body { background-color: #f4f5f7; }
    </style>
</head>
<body class="font-body-md text-on-surface">
{{-- Top Navigation Bar --}}
<header class="sticky top-0 w-full z-30 flex justify-between items-center px-gutter py-md bg-white dark:bg-on-surface shadow-sm">
    <div class="flex items-center gap-lg">
        <a href="{{ route('resident.index') }}" class="font-headline-lg text-headline-lg font-black text-primary dark:text-primary-fixed">CondoPro</a>
        <nav class="hidden md:flex gap-lg ml-xl">
            <a class="{{ request()->routeIs('resident.index') ? 'text-primary font-semibold border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary' }} font-body-lg text-body-lg transition-colors" href="{{ route('resident.index') }}">{{ __('messages.resident.my_balance') }}</a>
            <a class="{{ request()->routeIs('resident.invoices*') ? 'text-primary font-semibold border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary' }} font-body-lg text-body-lg transition-colors" href="{{ route('resident.invoices') }}">{{ __('messages.resident.current_invoice') }}</a>
            <a class="{{ request()->routeIs('resident.condominium-fund*') ? 'text-primary font-semibold border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary' }} font-body-lg text-body-lg transition-colors" href="{{ route('resident.condominium-fund') }}">{{ app()->getLocale() === 'es' ? 'Fondo' : 'Fund' }}</a>
            <a class="{{ request()->routeIs('resident.history') ? 'text-primary font-semibold border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary' }} font-body-lg text-body-lg transition-colors" href="{{ route('resident.history') }}">{{ __('messages.resident.recent_history') }}</a>
        </nav>
    </div>
    <div class="flex items-center gap-md">
        @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'admin')
        <a href="{{ route('dashboard') }}" class="hidden md:flex items-center gap-1 px-md py-sm border border-outline-variant rounded-lg text-on-surface-variant hover:text-primary hover:border-primary transition-colors text-body-sm font-bold">
            <span class="material-symbols-outlined text-lg">swap_horiz</span>
            {{ __('messages.nav.admin_portal') }}
        </a>
        @endif
        {{-- Language toggle --}}
        <a href="{{ route('lang.switch', app()->getLocale() === 'es' ? 'en' : 'es') }}" class="text-on-surface-variant hover:text-primary font-body-sm font-bold px-sm py-xs border border-outline-variant/50 rounded transition-colors">
            {{ app()->getLocale() === 'es' ? 'EN' : 'ES' }}
        </a>
        {{-- User dropdown --}}
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button @click="open = !open" class="flex items-center gap-sm cursor-pointer">
                <div class="w-9 h-9 rounded-full border-2 border-primary-container bg-primary/10 flex items-center justify-center text-primary font-bold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            </button>
            <div x-show="open" x-transition class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-outline-variant/20 py-sm z-50">
                <div class="px-lg py-md border-b border-outline-variant/20">
                    <p class="font-bold text-on-surface">{{ auth()->user()->name }}</p>
                    <p class="text-body-sm text-on-surface-variant">{{ auth()->user()->email }}</p>
                </div>
                @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'admin')
                <a href="{{ route('dashboard') }}" class="flex items-center gap-md px-lg py-sm text-on-surface-variant hover:text-primary hover:bg-surface-container-low transition-colors">
                    <span class="material-symbols-outlined text-lg">admin_panel_settings</span>
                    <span class="text-body-md">{{ __('messages.nav.admin_portal') }}</span>
                </a>
                @endif
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-md px-lg py-sm text-on-surface-variant hover:text-primary hover:bg-surface-container-low transition-colors">
                    <span class="material-symbols-outlined text-lg">person</span>
                    <span class="text-body-md">{{ __('messages.nav.profile') }}</span>
                </a>
                <div class="my-xs border-t border-outline-variant/20"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-md px-lg py-sm text-error hover:bg-[#FFEBE6] transition-colors">
                        <span class="material-symbols-outlined text-lg">logout</span>
                        <span class="text-body-md">{{ __('messages.nav.logout') }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

{{-- Main Content --}}
<main class="max-w-[1440px] mx-auto px-4 md:px-margin py-lg space-y-lg">
    @yield('content')
</main>

{{-- Bottom Navigation Bar Mobile --}}
<nav class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-4 py-2 bg-white dark:bg-on-surface shadow-2xl md:hidden border-t border-outline-variant/10">
    <a class="flex flex-col items-center justify-center {{ request()->routeIs('resident.index') ? 'bg-secondary-container text-on-secondary-container' : 'text-on-surface-variant' }} rounded-xl px-3 py-1" href="{{ route('resident.index') }}">
        <span class="material-symbols-outlined">account_balance</span>
        <span class="font-label-caps text-label-caps">{{ __('messages.resident.my_balance') }}</span>
    </a>
    <a class="flex flex-col items-center justify-center {{ request()->routeIs('resident.invoices*') ? 'bg-secondary-container text-on-secondary-container' : 'text-on-surface-variant' }} rounded-xl px-3 py-1" href="{{ route('resident.invoices') }}">
        <span class="material-symbols-outlined">description</span>
        <span class="font-label-caps text-label-caps">{{ __('messages.resident.current_invoice') }}</span>
    </a>
    <a class="flex flex-col items-center justify-center {{ request()->routeIs('resident.condominium-fund*') ? 'bg-secondary-container text-on-secondary-container' : 'text-on-surface-variant' }} rounded-xl px-3 py-1" href="{{ route('resident.condominium-fund') }}">
        <span class="material-symbols-outlined">savings</span>
        <span class="font-label-caps text-label-caps">{{ app()->getLocale() === 'es' ? 'Fondo' : 'Fund' }}</span>
    </a>
    <a class="flex flex-col items-center justify-center {{ request()->routeIs('resident.vouchers*') ? 'bg-secondary-container text-on-secondary-container' : 'text-on-surface-variant' }} rounded-xl px-3 py-1" href="{{ route('resident.vouchers.upload') }}">
        <span class="material-symbols-outlined">cloud_upload</span>
        <span class="font-label-caps text-label-caps">{{ __('messages.resident.upload_voucher') }}</span>
    </a>
</nav>

{{-- Spacer for Bottom Nav on Mobile --}}
<div class="h-20 md:hidden"></div>

@stack('scripts')
</body>
</html>