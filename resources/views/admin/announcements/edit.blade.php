@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm">chevron_right</span>
    <a href="{{ route('announcements.index') }}" class="text-body-sm hover:text-primary transition-colors">{{ app()->getLocale() === 'es' ? 'Avisos' : 'Announcements' }}</a>
    <span class="material-symbols-outlined text-sm">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ app()->getLocale() === 'es' ? 'Editar Aviso' : 'Edit Announcement' }}</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">{{ app()->getLocale() === 'es' ? 'Editar Aviso' : 'Edit Announcement' }}</h2>
        <p class="text-on-surface-variant">{{ $announcement->title }}</p>
    </div>
    <a href="{{ route('announcements.index') }}" class="px-lg py-md bg-white border border-outline-variant rounded-lg flex items-center gap-sm hover:bg-surface-container-low transition-colors">
        <span class="material-symbols-outlined">arrow_back</span>
        {{ __('messages.common.back_to_list') }}
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-lg max-w-3xl">
    <form action="{{ route('announcements.update', $announcement) }}" method="POST">
        @csrf @method('PUT')
        <div class="space-y-lg">
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Condominio' : 'Condominium' }}</label>
                <select name="condominium_id" disabled class="w-full px-md py-sm border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface opacity-60">
                    <option>{{ $announcement->condominium->name }}</option>
                </select>
                <input type="hidden" name="condominium_id" value="{{ $announcement->condominium_id }}">
            </div>

            <div>
                <label for="title" class="block font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Título' : 'Title' }} <span class="text-error">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title', $announcement->title) }}" required class="w-full px-md py-sm border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all">
                @error('title') <p class="text-error text-body-sm mt-xs">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="body" class="block font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Contenido' : 'Content' }} <span class="text-error">*</span></label>
                <textarea id="body" name="body" rows="6" required class="w-full px-md py-sm border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all">{{ old('body', $announcement->body) }}</textarea>
                @error('body') <p class="text-error text-body-sm mt-xs">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
                <div>
                    <label for="priority" class="block font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Prioridad' : 'Priority' }} <span class="text-error">*</span></label>
                    <select id="priority" name="priority" required class="w-full px-md py-sm border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all">
                        <option value="low" {{ old('priority', $announcement->priority) === 'low' ? 'selected' : '' }}>{{ app()->getLocale() === 'es' ? 'Baja' : 'Low' }}</option>
                        <option value="normal" {{ old('priority', $announcement->priority) === 'normal' ? 'selected' : '' }}>{{ app()->getLocale() === 'es' ? 'Normal' : 'Normal' }}</option>
                        <option value="high" {{ old('priority', $announcement->priority) === 'high' ? 'selected' : '' }}>{{ app()->getLocale() === 'es' ? 'Alta' : 'High' }}</option>
                        <option value="urgent" {{ old('priority', $announcement->priority) === 'urgent' ? 'selected' : '' }}>{{ app()->getLocale() === 'es' ? 'Urgente' : 'Urgent' }}</option>
                    </select>
                </div>
                <div>
                    <label for="published_at" class="block font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Fecha de publicación' : 'Publish date' }}</label>
                    <input type="datetime-local" id="published_at" name="published_at" value="{{ old('published_at', $announcement->published_at?->format('Y-m-d\TH:i')) }}" class="w-full px-md py-sm border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
                <div>
                    <label for="expires_at" class="block font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Fecha de expiración' : 'Expires at' }}</label>
                    <input type="datetime-local" id="expires_at" name="expires_at" value="{{ old('expires_at', $announcement->expires_at?->format('Y-m-d\TH:i')) }}" class="w-full px-md py-sm border border-outline-variant rounded-lg bg-surface-container-lowest text-on-surface focus:ring-2 focus:ring-primary-container focus:border-primary-container transition-all">
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-sm cursor-pointer">
                        <input type="checkbox" name="is_pinned" value="1" {{ old('is_pinned', $announcement->is_pinned) ? 'checked' : '' }} class="w-5 h-5 rounded border-outline-variant text-primary focus:ring-primary-container">
                        <span class="text-body-md text-on-surface font-medium">{{ app()->getLocale() === 'es' ? 'Fijar aviso' : 'Pin announcement' }}</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-md pt-sm border-t border-outline-variant/30">
                <button type="submit" class="px-lg py-md bg-primary text-on-primary rounded-lg flex items-center gap-sm font-bold shadow-sm hover:bg-primary-container hover:text-on-primary-container transition-colors">
                    <span class="material-symbols-outlined">save</span>
                    {{ app()->getLocale() === 'es' ? 'Guardar Cambios' : 'Save Changes' }}
                </button>
                <a href="{{ route('announcements.index') }}" class="px-lg py-md bg-white border border-outline-variant rounded-lg flex items-center gap-sm hover:bg-surface-container-low transition-colors">
                    {{ __('messages.common.cancel') }}
                </a>
            </div>
        </div>
    </form>
</div>
@endsection