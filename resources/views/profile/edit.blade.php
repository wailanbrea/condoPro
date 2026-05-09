@extends(auth()->user()->role === 'resident' ? 'layouts.resident' : 'layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">{{ auth()->user()->role === 'resident' ? __('messages.nav.resident_portal') : __('messages.nav.dashboard') }}</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ __('messages.nav.profile') }}</span>
</nav>

<div class="max-w-2xl mx-auto space-y-lg">
    {{-- Header --}}
    <div class="flex items-center gap-lg">
        <div class="w-16 h-16 rounded-full border-2 border-primary-container bg-primary/10 flex items-center justify-center text-primary font-bold text-2xl">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>
        <div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">{{ auth()->user()->name }}</h2>
            <p class="text-on-surface-variant font-body-md">{{ auth()->user()->email }}</p>
            <span class="inline-block mt-xs px-sm py-xs bg-primary/10 text-primary text-label-caps rounded uppercase font-bold">{{ auth()->user()->role === 'super_admin' ? 'Super Admin' : ucfirst(auth()->user()->role) }}</span>
        </div>
    </div>

    {{-- Profile Information --}}
    <div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] overflow-hidden">
        <div class="px-lg py-md bg-primary text-white flex items-center gap-sm">
            <span class="material-symbols-outlined" data-icon="person">person</span>
            <h3 class="font-headline-md">{{ app()->getLocale() === 'es' ? 'Información Personal' : 'Personal Information' }}</h3>
        </div>
        <form method="POST" action="{{ route('profile.update') }}" class="p-lg space-y-lg">
            @csrf
            @method('patch')

            @if(session('status') === 'profile-updated')
                <div class="px-lg py-md bg-[#E3FCEF] text-[#006644] rounded-lg flex items-center gap-2 border border-[#006644]/20">
                    <span class="material-symbols-outlined" data-icon="check_circle">check_circle</span>
                    {{ app()->getLocale() === 'es' ? 'Perfil actualizado correctamente.' : 'Profile updated successfully.' }}
                </div>
            @endif

            <div>
                <label for="name" class="block font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Nombre completo' : 'Full Name' }}</label>
                <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required autofocus autocomplete="name"
                    class="w-full px-md py-sm border border-outline-variant/50 rounded-lg bg-white text-on-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                @error('name')
                    <p class="text-error text-body-sm mt-xs">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Correo electrónico' : 'Email Address' }}</label>
                <input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required autocomplete="username"
                    class="w-full px-md py-sm border border-outline-variant/50 rounded-lg bg-white text-on-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                @error('email')
                    <p class="text-error text-body-sm mt-xs">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="block font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Teléfono' : 'Phone' }}</label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}" autocomplete="tel"
                    class="w-full px-md py-sm border border-outline-variant/50 rounded-lg bg-white text-on-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
            </div>

            <div class="flex justify-end pt-sm">
                <button type="submit" class="flex items-center justify-center gap-sm bg-primary text-white px-xl py-md rounded-lg font-bold shadow-sm hover:brightness-110 transition-all active:scale-[0.98]">
                    <span class="material-symbols-outlined">save</span>
                    {{ app()->getLocale() === 'es' ? 'Guardar Cambios' : 'Save Changes' }}
                </button>
            </div>
        </form>
    </div>

    {{-- Change Password --}}
    <div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] overflow-hidden">
        <div class="px-lg py-md bg-surface-container-low border-b border-outline-variant/20 flex items-center gap-sm">
            <span class="material-symbols-outlined text-on-surface-variant" data-icon="lock">lock</span>
            <h3 class="font-headline-md text-on-surface">{{ app()->getLocale() === 'es' ? 'Cambiar Contraseña' : 'Change Password' }}</h3>
        </div>
        <form method="POST" action="{{ route('password.update') }}" class="p-lg space-y-lg">
            @csrf
            @method('put')

            @if(session('status') === 'password-updated')
                <div class="px-lg py-md bg-[#E3FCEF] text-[#006644] rounded-lg flex items-center gap-2 border border-[#006644]/20">
                    <span class="material-symbols-outlined" data-icon="check_circle">check_circle</span>
                    {{ app()->getLocale() === 'es' ? 'Contraseña actualizada correctamente.' : 'Password updated successfully.' }}
                </div>
            @endif

            <div>
                <label for="current_password" class="block font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Contraseña actual' : 'Current Password' }}</label>
                <input type="password" id="current_password" name="current_password" required autocomplete="current-password"
                    class="w-full px-md py-sm border border-outline-variant/50 rounded-lg bg-white text-on-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                @error('current_password')
                    <p class="text-error text-body-sm mt-xs">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Nueva contraseña' : 'New Password' }}</label>
                <input type="password" id="password" name="password" required autocomplete="new-password"
                    class="w-full px-md py-sm border border-outline-variant/50 rounded-lg bg-white text-on-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                @error('password')
                    <p class="text-error text-body-sm mt-xs">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Confirmar nueva contraseña' : 'Confirm New Password' }}</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                    class="w-full px-md py-sm border border-outline-variant/50 rounded-lg bg-white text-on-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
            </div>

            <div class="flex justify-end pt-sm">
                <button type="submit" class="flex items-center justify-center gap-sm bg-primary text-white px-xl py-md rounded-lg font-bold shadow-sm hover:brightness-110 transition-all active:scale-[0.98]">
                    <span class="material-symbols-outlined">lock_reset</span>
                    {{ app()->getLocale() === 'es' ? 'Cambiar Contraseña' : 'Update Password' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection