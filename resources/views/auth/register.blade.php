<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'CondoPro') }} - {{ __('messages.auth.register') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    "colors": {
                        "surface-tint": "#0c56d0",
                        "primary-container": "#0052cc",
                        "primary": "#003d9b",
                        "on-primary": "#ffffff",
                        "on-surface": "#091c35",
                        "on-surface-variant": "#434654",
                        "surface": "#f9f9ff",
                        "surface-container-low": "#f0f3ff",
                        "surface-container-lowest": "#ffffff",
                        "surface-container": "#e7eeff",
                        "outline": "#737685",
                        "outline-variant": "#c3c6d6",
                        "error": "#ba1a1a",
                        "on-error": "#ffffff",
                        "secondary": "#006c47",
                        "secondary-container": "#82f9be",
                    },
                    "fontFamily": {
                        "body-md": ["Inter"],
                        "headline-md": ["Inter"],
                        "headline-lg": ["Inter"],
                        "display-xl": ["Inter"],
                        "label-caps": ["Inter"],
                    },
                    "fontSize": {
                        "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                        "headline-md": ["20px", {"lineHeight": "28px", "letterSpacing": "-0.01em", "fontWeight": "600"}],
                        "headline-lg": ["24px", {"lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600"}],
                        "display-xl": ["36px", {"lineHeight": "44px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "label-caps": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "700"}],
                    },
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
    </style>
</head>
<body class="min-h-screen bg-surface flex items-center justify-center relative">
    <div class="absolute top-4 right-4 flex gap-2 z-10">
        @php
            $currentLocale = app()->getLocale();
        @endphp
        <a href="{{ route('lang.switch', app()->getLocale() === 'es' ? 'en' : 'es') }}"
           class="px-3 py-1 rounded-lg text-sm font-bold border {{ $currentLocale === 'en' ? 'bg-primary-container text-white' : 'bg-white text-on-surface-variant border-outline-variant' }} hover:bg-surface-container transition-colors">
            EN
        </a>
        <a href="{{ route('lang.switch', app()->getLocale() === 'en' ? 'es' : 'en') }}"
           class="px-3 py-1 rounded-lg text-sm font-bold border {{ $currentLocale === 'es' ? 'bg-primary-container text-white' : 'bg-white text-on-surface-variant border-outline-variant' }} hover:bg-surface-container transition-colors">
            ES
        </a>
    </div>

    <div class="w-full max-w-md px-6">
        <div class="bg-white rounded-2xl shadow-[0px_4px_20px_rgba(0,0,0,0.08)] p-8 sm:p-10">
            <div class="flex flex-col items-center mb-8">
                <div class="w-16 h-16 bg-primary-container rounded-2xl flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-white" style="font-size:32px" data-icon="apartment">apartment</span>
                </div>
                <h1 class="font-display-xl text-display-xl text-on-surface">CondoPro</h1>
                <p class="text-on-surface-variant text-body-md mt-1">{{ __('messages.auth.register_title') }}</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-5">
                    <label for="name" class="block text-on-surface font-label-caps text-label-caps mb-2">
                        {{ __('messages.auth.name') }}
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline" data-icon="person">person</span>
                        <input id="name" type="text" name="name"
                               value="{{ old('name') }}" required autofocus autocomplete="name"
                               class="w-full pl-10 pr-4 py-3 bg-surface-container-low border border-outline-variant rounded-lg text-body-md text-on-surface placeholder-on-surface-variant focus:outline-none focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all"
                               placeholder="{{ __('messages.auth.name') }}">
                    </div>
                    @error('name')
                        <p class="mt-1 text-error text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-5">
                    <label for="email" class="block text-on-surface font-label-caps text-label-caps mb-2">
                        {{ __('messages.auth.email') }}
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline" data-icon="mail">mail</span>
                        <input id="email" type="email" name="email"
                               value="{{ old('email') }}" required autocomplete="username"
                               class="w-full pl-10 pr-4 py-3 bg-surface-container-low border border-outline-variant rounded-lg text-body-md text-on-surface placeholder-on-surface-variant focus:outline-none focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all"
                               placeholder="{{ __('messages.auth.email') }}">
                    </div>
                    @error('email')
                        <p class="mt-1 text-error text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-5">
                    <label for="password" class="block text-on-surface font-label-caps text-label-caps mb-2">
                        {{ __('messages.auth.password') }}
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline" data-icon="lock">lock</span>
                        <input id="password" type="password" name="password" required autocomplete="new-password"
                               class="w-full pl-10 pr-4 py-3 bg-surface-container-low border border-outline-variant rounded-lg text-body-md text-on-surface placeholder-on-surface-variant focus:outline-none focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all"
                               placeholder="{{ __('messages.auth.password') }}">
                    </div>
                    @error('password')
                        <p class="mt-1 text-error text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="password_confirmation" class="block text-on-surface font-label-caps text-label-caps mb-2">
                        {{ __('messages.auth.confirm_password') }}
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline" data-icon="lock">lock</span>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                               class="w-full pl-10 pr-4 py-3 bg-surface-container-low border border-outline-variant rounded-lg text-body-md text-on-surface placeholder-on-surface-variant focus:outline-none focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all"
                               placeholder="{{ __('messages.auth.confirm_password') }}">
                    </div>
                    @error('password_confirmation')
                        <p class="mt-1 text-error text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full py-3 bg-primary-container text-white font-bold rounded-lg hover:brightness-110 transition-all focus:outline-none focus:ring-2 focus:ring-primary-container focus:ring-offset-2">
                    {{ __('messages.auth.register') }}
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-on-surface-variant text-body-md">
                    {{ __('messages.auth.already_registered') }}
                    <a href="{{ route('login') }}" class="text-primary-container font-bold hover:underline">{{ __('messages.auth.login') }}</a>
                </p>
            </div>
        </div>

        <p class="text-center text-on-surface-variant text-sm mt-6">
            &copy; {{ date('Y') }} CondoPro. All rights reserved.
        </p>
    </div>
</body>
</html>