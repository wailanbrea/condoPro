@extends('layouts.resident')

@section('content')
<section class="space-y-lg">
    <div class="flex items-center gap-sm">
        <span class="material-symbols-outlined text-primary text-3xl">campaign</span>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">{{ app()->getLocale() === 'es' ? 'Avisos del Condominio' : 'Condominium Announcements' }}</h1>
    </div>

    @if($announcements->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-xl text-center">
            <span class="material-symbols-outlined text-5xl text-on-surface-variant/30 mb-md">campaign</span>
            <p class="text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'No hay avisos publicados.' : 'No announcements published.' }}</p>
        </div>
    @else
        @foreach($announcements as $announcement)
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden {{ $announcement->is_pinned ? 'ring-2 ring-primary/30' : '' }} {{ $announcement->priority === 'urgent' ? 'border-l-4 border-l-red-500' : ($announcement->priority === 'high' ? 'border-l-4 border-l-amber-500' : '') }}">
            <div class="p-lg">
                <div class="flex items-center gap-sm mb-xs">
                    @if($announcement->is_pinned)
                        <span class="material-symbols-outlined text-primary text-lg" style="font-variation-settings: 'FILL' 1;">push_pin</span>
                    @endif
                    @if($announcement->priority === 'urgent')
                        <span class="inline-flex items-center px-sm py-0.5 rounded bg-red-100 text-red-800 text-xs font-semibold">URGENTE</span>
                    @elseif($announcement->priority === 'high')
                        <span class="inline-flex items-center px-sm py-0.5 rounded bg-amber-100 text-amber-800 text-xs font-semibold">IMPORTANTE</span>
                    @endif
                    <span class="text-body-sm text-on-surface-variant">{{ $announcement->published_at ? $announcement->published_at->format('d M, Y') : '-' }}</span>
                </div>
                <h3 class="font-headline-md text-headline-md text-on-surface">{{ $announcement->title }}</h3>
                <div class="text-body-md text-on-surface-variant mt-sm whitespace-pre-line">{{ $announcement->body }}</div>
                <div class="flex items-center gap-sm mt-md text-body-sm text-on-surface-variant/70">
                    <span class="material-symbols-outlined text-md">person</span>
                    <span>{{ $announcement->creator->name }}</span>
                </div>
            </div>
        </div>
        @endforeach

        <div class="mt-lg">{{ $announcements->links() }}</div>
    @endif
</section>
@endsection