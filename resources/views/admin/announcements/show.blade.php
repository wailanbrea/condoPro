@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm">chevron_right</span>
    <a href="{{ route('announcements.index') }}" class="text-body-sm hover:text-primary transition-colors">{{ app()->getLocale() === 'es' ? 'Avisos' : 'Announcements' }}</a>
    <span class="material-symbols-outlined text-sm">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ app()->getLocale() === 'es' ? 'Detalle' : 'Detail' }}</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">{{ $announcement->title }}</h2>
        <p class="text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Detalle del aviso' : 'Announcement detail' }}</p>
    </div>
    <div class="flex items-center gap-sm">
        <a href="{{ route('announcements.edit', $announcement) }}" class="px-lg py-md bg-primary text-on-primary rounded-lg flex items-center gap-sm font-bold shadow-sm hover:bg-primary-container hover:text-on-primary-container transition-colors">
            <span class="material-symbols-outlined">edit</span>
            {{ app()->getLocale() === 'es' ? 'Editar' : 'Edit' }}
        </a>
        <a href="{{ route('announcements.index') }}" class="px-lg py-md bg-white border border-outline-variant rounded-lg flex items-center gap-sm hover:bg-surface-container-low transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
            {{ app()->getLocale() === 'es' ? 'Volver' : 'Back' }}
        </a>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
    <div class="p-lg">
        <div class="flex items-center gap-sm mb-md">
            @if($announcement->is_pinned)
                <span class="material-symbols-outlined text-primary text-xl" style="font-variation-settings: 'FILL' 1;">push_pin</span>
            @endif
            @switch($announcement->priority)
                @case('urgent')
                    <span class="inline-flex items-center px-sm py-0.5 rounded bg-red-100 text-red-800 text-xs font-semibold uppercase tracking-wider">{{ app()->getLocale() === 'es' ? 'Urgente' : 'Urgent' }}</span>
                    @break
                @case('high')
                    <span class="inline-flex items-center px-sm py-0.5 rounded bg-amber-100 text-amber-800 text-xs font-semibold uppercase tracking-wider">{{ app()->getLocale() === 'es' ? 'Alta' : 'High' }}</span>
                    @break
                @case('normal')
                    <span class="inline-flex items-center px-sm py-0.5 rounded bg-blue-100 text-blue-800 text-xs font-semibold uppercase tracking-wider">{{ app()->getLocale() === 'es' ? 'Normal' : 'Normal' }}</span>
                    @break
                @case('low')
                    <span class="inline-flex items-center px-sm py-0.5 rounded bg-gray-100 text-gray-800 text-xs font-semibold uppercase tracking-wider">{{ app()->getLocale() === 'es' ? 'Baja' : 'Low' }}</span>
                    @break
            @endswitch
            <span class="text-body-sm text-on-surface-variant">{{ $announcement->published_at?->format('d M, Y H:i') ?? '-' }}</span>
        </div>

        <div class="space-y-md mt-lg">
            <div>
                <label class="font-label-caps text-label-caps text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Condominio' : 'Condominium' }}</label>
                <p class="text-body-lg text-on-surface mt-xs">{{ $announcement->condominium->name }}</p>
            </div>
            <div>
                <label class="font-label-caps text-label-caps text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Publicado por' : 'Published by' }}</label>
                <p class="text-body-lg text-on-surface mt-xs">{{ $announcement->creator->name }}</p>
            </div>
            <div>
                <label class="font-label-caps text-label-caps text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Contenido' : 'Content' }}</label>
                <div class="mt-xs p-md bg-surface-container-low rounded-lg text-on-surface whitespace-pre-line">{{ $announcement->body }}</div>
            </div>
            @if($announcement->expires_at)
                <div>
                    <label class="font-label-caps text-label-caps text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Fecha de expiración' : 'Expires at' }}</label>
                    <p class="text-body-lg text-on-surface mt-xs">{{ $announcement->expires_at->format('d M, Y H:i') }}</p>
                </div>
            @endif
        </div>
    </div>

    <div class="px-lg py-md border-t border-outline-variant/30 bg-surface-container-low/50">
        <div class="flex items-center justify-between">
            <span class="text-body-sm text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Creado' : 'Created' }}: {{ $announcement->created_at->format('d/m/Y H:i') }}</span>
            <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" onsubmit="return confirm('{{ app()->getLocale() === 'es' ? '¿Eliminar este aviso?' : 'Delete this announcement?' }}')">
                @csrf @method('DELETE')
                <button type="submit" class="px-md py-sm text-error hover:bg-red-50 rounded-lg flex items-center gap-sm text-body-sm transition-colors">
                    <span class="material-symbols-outlined text-sm">delete</span>
                    {{ app()->getLocale() === 'es' ? 'Eliminar' : 'Delete' }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection