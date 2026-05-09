@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm">chevron_right</span>
    <a href="{{ route('announcements.index') }}" class="text-body-sm hover:text-primary transition-colors">{{ app()->getLocale() === 'es' ? 'Avisos' : 'Announcements' }}</a>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">{{ app()->getLocale() === 'es' ? 'Avisos del Condominio' : 'Condominium Announcements' }}</h2>
        <p class="text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Administra avisos y comunicaciones' : 'Manage announcements and communications' }}</p>
    </div>
    <a href="{{ route('announcements.create') }}" class="px-lg py-md bg-primary text-on-primary rounded-lg flex items-center gap-sm hover:bg-primary-container hover:text-on-primary-container transition-colors shadow-sm">
        <span class="material-symbols-outlined">add</span>
        {{ app()->getLocale() === 'es' ? 'Nuevo Aviso' : 'New Announcement' }}
    </a>
</div>

<div class="space-y-md">
    @foreach($announcements as $announcement)
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden {{ $announcement->is_pinned ? 'ring-2 ring-primary/30' : '' }}">
        <div class="p-lg">
            <div class="flex items-start justify-between gap-md">
                <div class="flex-1">
                    <div class="flex items-center gap-sm mb-xs">
                        @if($announcement->is_pinned)
                            <span class="material-symbols-outlined text-primary text-lg" style="font-variation-settings: 'FILL' 1;">push_pin</span>
                        @endif
                        @if($announcement->priority === 'urgent')
                            <span class="inline-flex items-center px-sm py-0.5 rounded bg-red-100 text-red-800 text-xs font-semibold">{{ strtoupper($announcement->priority) }}</span>
                        @elseif($announcement->priority === 'high')
                            <span class="inline-flex items-center px-sm py-0.5 rounded bg-amber-100 text-amber-800 text-xs font-semibold">{{ strtoupper($announcement->priority) }}</span>
                        @endif
                        <span class="text-body-sm text-on-surface-variant">{{ $announcement->published_at ? $announcement->published_at->format('d M, Y') : '-' }}</span>
                    </div>
                    <h3 class="font-headline-md text-headline-md text-on-surface">{{ $announcement->title }}</h3>
                    <p class="text-body-md text-on-surface-variant mt-xs line-clamp-2">{{ Str::limit($announcement->body, 150) }}</p>
                    <div class="flex items-center gap-sm mt-sm text-body-sm text-on-surface-variant">
                        <span>{{ $announcement->condominium->name }}</span>
                        <span>·</span>
                        <span>{{ $announcement->creator->name }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-sm">
                    <a href="{{ route('announcements.show', $announcement) }}" class="p-sm rounded-lg hover:bg-surface-container-low transition-colors">
                        <span class="material-symbols-outlined text-on-surface-variant">visibility</span>
                    </a>
                    <a href="{{ route('announcements.edit', $announcement) }}" class="p-sm rounded-lg hover:bg-surface-container-low transition-colors">
                        <span class="material-symbols-outlined text-on-surface-variant">edit</span>
                    </a>
                    <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" onsubmit="return confirm('{{ app()->getLocale() === 'es' ? '¿Eliminar este aviso?' : 'Delete this announcement?' }}')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-sm rounded-lg hover:bg-red-50 transition-colors">
                            <span class="material-symbols-outlined text-error">delete</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    @if($announcements->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-xl text-center">
        <span class="material-symbols-outlined text-5xl text-on-surface-variant/30 mb-md">campaign</span>
        <p class="text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'No hay avisos publicados.' : 'No announcements published.' }}</p>
    </div>
    @endif
</div>

<div class="mt-lg">{{ $announcements->links() }}</div>
@endsection