<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'CondoPro') }} - {{ __('messages.nav.dashboard') }}</title>
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
        body {
            background-color: #F4F5F7;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #DFE1E6;
            border-radius: 10px;
        }
    </style>
</head>
<body class="font-body-md text-on-surface">
@php
    $sidebarItems = [
        ['route' => 'dashboard', 'icon' => 'dashboard', 'label' => __('messages.nav.dashboard')],
        ['route' => 'apartments.index', 'icon' => 'domain', 'label' => __('messages.nav.apartments')],
        ['route' => 'users.index', 'icon' => 'groups', 'label' => __('messages.nav.residents')],
        ['route' => 'billing.index', 'icon' => 'receipt_long', 'label' => __('messages.nav.billing')],
        ['route' => 'gas.index', 'icon' => 'local_gas_station', 'label' => __('messages.nav.gas')],
        ['route' => 'admin.gas.inventory', 'icon' => 'propane_tank', 'label' => app()->getLocale() === 'es' ? 'Inventario Gas' : 'Gas Inventory'],
        ['route' => 'gas-deliveries.index', 'icon' => 'local_shipping', 'label' => app()->getLocale() === 'es' ? 'Recepciones Gas' : 'Gas Deliveries'],
        ['route' => 'gas-tank.edit', 'icon' => 'tune', 'label' => app()->getLocale() === 'es' ? 'Config. Tanque' : 'Tank Settings'],
        ['route' => 'extra-charges.index', 'icon' => 'warning', 'label' => app()->getLocale() === 'es' ? 'Imprevistos' : 'Extra Charges'],
        ['route' => 'payments.index', 'icon' => 'payments', 'label' => __('messages.nav.payments'), 'badge' => \App\Models\Payment::when(auth()->user()->role === 'admin', fn($q) => $q->where('condominium_id', auth()->user()->condominium_id))->where('status', 'pending')->count()],
        ['route' => 'announcements.index', 'icon' => 'campaign', 'label' => app()->getLocale() === 'es' ? 'Avisos' : 'Announcements'],
        ['route' => 'expenses.index', 'icon' => 'account_balance_wallet', 'label' => __('messages.nav.expenses')],
        ['route' => 'expense-categories.index', 'icon' => 'category', 'label' => app()->getLocale() === 'es' ? 'Categorías' : 'Categories'],
        ['route' => 'financial-reports.index', 'icon' => 'account_balance', 'label' => 'Informe Financiero'],
        ['route' => 'condominium-fund.history', 'icon' => 'savings', 'label' => app()->getLocale() === 'es' ? 'Fondo del Condominio' : 'Condominium Fund'],
        ['route' => 'reports.index', 'icon' => 'analytics', 'label' => __('messages.nav.reports')],
        ['route' => 'condominiums.index', 'icon' => 'settings', 'label' => __('messages.nav.settings')],
    ];
    $currentRoute = request()->route() ? request()->route()->getName() : '';
@endphp

{{-- SideNavBar --}}
<aside class="fixed left-0 top-0 h-full z-40 h-screen w-64 flex-col hidden md:flex bg-surface-container-lowest dark:bg-on-surface shadow-sm dark:shadow-none transition-all duration-200 ease-in-out">
    <div class="px-lg py-xl">
        <h1 class="font-headline-md text-headline-md font-bold text-primary dark:text-primary-fixed">CondoPro Admin</h1>
        <p class="font-body-sm text-on-surface-variant opacity-70">Elite Management</p>
    </div>
    <nav class="flex-1 px-md overflow-y-auto custom-scrollbar space-y-1">
        @foreach ($sidebarItems as $item)
            @php
                $isActive = str_starts_with($currentRoute, str_replace('.index', '', $item['route'])) || $currentRoute === $item['route'];
            @endphp
            <a class="flex items-center gap-md px-md py-sm rounded-lg {{ $isActive ? 'text-primary dark:text-primary-fixed font-bold border-r-4 border-primary bg-surface-container-low' : 'text-on-surface-variant dark:text-outline-variant hover:text-primary hover:bg-surface-container-low' }} transition-all duration-200" href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}">
                <span class="material-symbols-outlined" data-icon="{{ $item['icon'] }}">{{ $item['icon'] }}</span>
                <span class="flex-1">{{ $item['label'] }}</span>
                @if(($item['badge'] ?? 0) > 0)
                <span class="min-w-[20px] h-[20px] bg-error text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1">{{ $item['badge'] > 99 ? '99+' : $item['badge'] }}</span>
                @endif
            </a>
        @endforeach
    </nav>
    <div class="p-lg mt-auto space-y-4">
        <div class="flex flex-col gap-2 border-t border-outline-variant pt-lg">
            <a class="flex items-center gap-md text-on-surface-variant hover:text-primary" href="#">
                <span class="material-symbols-outlined" data-icon="help">help</span>
                <span class="text-body-md">Help Center</span>
            </a>
        </div>
    </div>
</aside>

{{-- TopNavBar --}}
<header class="sticky top-0 w-full z-30 flex justify-between items-center px-gutter py-md bg-surface-container-lowest dark:bg-on-surface shadow-sm transition-all duration-150">
    <div class="flex items-center gap-xl md:ml-64">
        <div class="relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline" data-icon="search">search</span>
            <input class="pl-10 pr-4 py-2 bg-surface-container-low border-none rounded-full w-64 text-body-md focus:ring-2 focus:ring-primary-container transition-all" placeholder="{{ __('messages.common.search') }}" type="text"/>
        </div>
        <nav class="hidden lg:flex items-center gap-lg">
            <a class="text-on-surface-variant hover:text-primary transition-colors font-body-lg" href="#">{{ __('messages.resident.my_balance') }}</a>
            <a class="text-on-surface-variant hover:text-primary transition-colors font-body-lg" href="#">{{ __('messages.resident.current_invoice') }}</a>
            <a class="text-on-surface-variant hover:text-primary transition-colors font-body-lg" href="#">{{ __('messages.resident.recent_history') }}</a>
        </nav>
    </div>
    <div class="flex items-center gap-md">
        @php
            $unreadNotifications = \App\Models\Notification::forUser(auth()->user()->id)
                ->when(auth()->user()->role === 'admin', fn($q) => $q->where('condominium_id', auth()->user()->condominium_id))
                ->unread()
                ->count();
        @endphp
        <a href="{{ route('notifications.index') }}" class="p-2 text-on-surface-variant hover:bg-surface-container-low rounded-full transition-colors relative" title="{{ app()->getLocale() === 'es' ? 'Notificaciones' : 'Notifications' }}">
            <span class="material-symbols-outlined" data-icon="notifications">notifications</span>
            @if($unreadNotifications > 0)
            <span class="absolute top-0 right-0 min-w-[18px] h-[18px] bg-error text-white text-[10px] font-bold rounded-full flex items-center justify-center">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>
            @endif
        </a>
        <div class="h-8 w-[1px] bg-outline-variant mx-2"></div>
        {{-- User dropdown --}}
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button @click="open = !open" class="flex items-center gap-sm cursor-pointer group">
                <div class="text-right hidden sm:block">
                    <p class="font-body-md font-bold leading-none">{{ auth()->user()->name }}</p>
                    <p class="text-body-sm opacity-60">{{ auth()->user()->role === 'super_admin' ? 'Super Admin' : ucfirst(auth()->user()->role) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full border-2 border-primary-container bg-primary/10 flex items-center justify-center text-primary font-bold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            </button>
            <div x-show="open" x-transition class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-outline-variant/20 py-sm z-50">
                <div class="px-lg py-md border-b border-outline-variant/20">
                    <p class="font-bold text-on-surface">{{ auth()->user()->name }}</p>
                    <p class="text-body-sm text-on-surface-variant">{{ auth()->user()->email }}</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-md px-lg py-sm text-on-surface-variant hover:text-primary hover:bg-surface-container-low transition-colors">
                    <span class="material-symbols-outlined text-lg">person</span>
                    <span class="text-body-md">{{ __('messages.nav.profile') }}</span>
                </a>
                @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'admin')
                <a href="{{ route('resident.index') }}" class="flex items-center gap-md px-lg py-sm text-on-surface-variant hover:text-primary hover:bg-surface-container-low transition-colors">
                    <span class="material-symbols-outlined text-lg">swap_horiz</span>
                    <span class="text-body-md">{{ __('messages.nav.resident_portal') }}</span>
                </a>
                @endif
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

{{-- Main Content Area --}}
<main class="md:ml-64 p-gutter pb-32">
    @yield('content')
</main>

{{-- BottomNavBar Mobile --}}
<nav class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-4 py-2 bg-white dark:bg-on-surface shadow-2xl md:hidden">
    <a class="flex flex-col items-center justify-center bg-secondary-container dark:bg-secondary text-on-secondary-container dark:text-on-secondary rounded-xl px-3 py-1 transition-all duration-200" href="{{ route('dashboard') }}">
        <span class="material-symbols-outlined" data-icon="account_balance">account_balance</span>
        <span class="font-label-caps text-[10px]">{{ __('messages.resident.my_balance') }}</span>
    </a>
    <a class="flex flex-col items-center justify-center text-on-surface-variant dark:text-outline-variant active:bg-surface-container-high transition-all duration-200" href="#">
        <span class="material-symbols-outlined" data-icon="description">description</span>
        <span class="font-label-caps text-[10px]">{{ __('messages.resident.current_invoice') }}</span>
    </a>
    <a class="flex flex-col items-center justify-center text-on-surface-variant dark:text-outline-variant active:bg-surface-container-high transition-all duration-200" href="#">
        <span class="material-symbols-outlined" data-icon="history">history</span>
        <span class="font-label-caps text-[10px]">{{ __('messages.resident.recent_history') }}</span>
    </a>
    <a class="flex flex-col items-center justify-center text-on-surface-variant dark:text-outline-variant active:bg-surface-container-high transition-all duration-200" href="#">
        <span class="material-symbols-outlined" data-icon="cloud_upload">cloud_upload</span>
        <span class="font-label-caps text-[10px]">{{ __('messages.resident.upload_voucher') }}</span>
    </a>
</nav>

@stack('scripts')
</body>
</html>