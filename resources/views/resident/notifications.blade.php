@extends('layouts.resident')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ app()->getLocale() === 'es' ? 'Notificaciones' : 'Notifications' }}</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">{{ app()->getLocale() === 'es' ? 'Notificaciones' : 'Notifications' }}</h2>
        <p class="text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Centro de notificaciones' : 'Notification center' }}</p>
    </div>
    <form action="{{ route('resident.notifications.markAllRead') }}" method="POST">
        @csrf
        <button type="submit" class="px-lg py-md bg-primary text-on-primary rounded-lg flex items-center gap-sm font-bold shadow-sm hover:bg-primary-container hover:text-on-primary-container transition-colors">
            <span class="material-symbols-outlined">done_all</span>
            {{ app()->getLocale() === 'es' ? 'Marcar todas como leídas' : 'Mark all as read' }}
        </button>
    </form>
</div>

@if(session('success'))
    <div class="mb-lg px-lg py-md bg-[#E3FCEF] text-[#006644] rounded-lg flex items-center gap-2 border border-[#006644]/20">
        <span class="material-symbols-outlined">check_circle</span>
        {{ session('success') }}
    </div>
@endif

<div class="space-y-md">
    @forelse($notifications as $notification)
        @php
            $wasUnread = in_array($notification->id, $unreadIds ?? []);
        @endphp
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden {{ $wasUnread ? 'ring-2 ring-primary/20 bg-primary/5' : '' }}">
            <div class="p-lg flex items-start gap-md">
                <div class="flex-shrink-0 mt-1">
                    @switch($notification->type)
                        @case('bill')
                            <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings: 'FILL' 1;">receipt_long</span>
                            @break
                        @case('payment')
                            <span class="material-symbols-outlined text-[#1B5E20] text-2xl" style="font-variation-settings: 'FILL' 1;">payments</span>
                            @break
                        @case('announcement')
                            <span class="material-symbols-outlined text-amber-700 text-2xl" style="font-variation-settings: 'FILL' 1;">campaign</span>
                            @break
                        @case('expense')
                            <span class="material-symbols-outlined text-[#B71C1C] text-2xl" style="font-variation-settings: 'FILL' 1;">trending_down</span>
                            @break
                        @case('warning')
                            <span class="material-symbols-outlined text-amber-700 text-2xl" style="font-variation-settings: 'FILL' 1;">warning</span>
                            @break
                        @default
                            <span class="material-symbols-outlined text-on-surface-variant text-2xl" style="font-variation-settings: 'FILL' 1;">notifications</span>
                    @endswitch
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-sm mb-xs">
                        @if($wasUnread)
                            <span class="inline-flex items-center px-sm py-0.5 rounded bg-primary/10 text-primary text-[11px] font-semibold uppercase tracking-wider">{{ app()->getLocale() === 'es' ? 'Nueva' : 'New' }}</span>
                        @endif
                        <span class="text-body-sm text-on-surface-variant">{{ $notification->created_at->format('d M, Y H:i') }}</span>
                    </div>
                    <h3 class="font-title-lg text-on-surface {{ $wasUnread ? 'font-bold' : '' }}">{{ $notification->title }}</h3>
                    <p class="text-body-md text-on-surface-variant mt-xs">{{ $notification->body }}</p>
                    @if($notification->condominium)
                        <span class="inline-flex items-center mt-sm px-sm py-0.5 rounded bg-surface-container-low text-on-surface-variant text-[11px] font-semibold uppercase tracking-wider">
                            {{ $notification->condominium->name }}
                        </span>
                    @endif
                </div>
                <div class="flex-shrink-0">
                    @if($wasUnread)
                        <span class="material-symbols-outlined text-primary" title="{{ app()->getLocale() === 'es' ? 'Marcada como leída' : 'Marked as read' }}">mark_email_read</span>
                    @else
                        <span class="material-symbols-outlined text-on-surface-variant/40">done_all</span>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-xl text-center">
            <span class="material-symbols-outlined text-5xl text-on-surface-variant/30 mb-md">notifications_off</span>
            <p class="font-headline-md text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'No hay notificaciones' : 'No notifications' }}</p>
            <p class="text-body-md text-on-surface-variant mt-xs">{{ app()->getLocale() === 'es' ? 'Las notificaciones aparecerán aquí cuando se generen.' : 'Notifications will appear here when they are generated.' }}</p>
        </div>
    @endforelse
</div>

<div class="mt-lg">{{ $notifications->links() }}</div>
@endsection