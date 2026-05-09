@extends('layouts.app')

@section('content')
<nav class="flex items-center gap-2 mb-xl text-on-surface-variant">
    <span class="material-symbols-outlined text-md" data-icon="home">home</span>
    <span class="text-body-sm">Home</span>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <a href="{{ route('users.index') }}" class="text-body-sm hover:text-primary transition-colors">{{ __('messages.users.title') }}</a>
    <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
    <span class="text-body-sm font-bold text-primary">{{ $user->name }}</span>
</nav>

<div class="flex justify-between items-end mb-lg">
    <div>
        <h2 class="font-display-xl text-display-xl text-on-surface">{{ $user->name }}</h2>
        <p class="text-on-surface-variant">{{ $user->email }}</p>
    </div>
    <div class="flex gap-md">
        <a href="{{ route('users.edit', $user) }}" class="px-lg py-2 bg-primary text-white rounded-lg flex items-center gap-2 hover:brightness-110 transition-all">
            <span class="material-symbols-outlined" data-icon="edit">edit</span>
            {{ __('messages.common.edit') }}
        </a>
        <a href="{{ route('users.index') }}" class="px-lg py-2 bg-white border border-outline-variant rounded-lg flex items-center gap-2 hover:bg-surface-container-low transition-colors">
            <span class="material-symbols-outlined" data-icon="arrow_back">arrow_back</span>
            {{ __('messages.common.back_to_list') }}
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-lg px-lg py-md bg-[#E3FCEF] text-[#006644] rounded-lg flex items-center gap-2 border border-[#006644]/20">
        <span class="material-symbols-outlined" data-icon="check_circle">check_circle</span>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-lg px-lg py-md bg-[#FFEBE6] text-[#BF2600] rounded-lg flex items-center gap-2 border border-[#BF2600]/20">
        <span class="material-symbols-outlined" data-icon="error">error</span>
        {{ session('error') }}
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter mb-xl">
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-primary">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.users.role') }}</p>
        @switch($user->role)
            @case('super_admin')
                <span class="px-3 py-1 bg-primary-fixed text-primary rounded-full text-[11px] font-bold uppercase tracking-wider">{{ __('messages.users.super_admin') }}</span>
                @break
            @case('admin')
                <span class="px-3 py-1 bg-secondary-container text-on-secondary rounded-full text-[11px] font-bold uppercase tracking-wider">{{ __('messages.users.admin') }}</span>
                @break
            @case('resident')
                <span class="px-3 py-1 bg-tertiary-fixed text-tertiary rounded-full text-[11px] font-bold uppercase tracking-wider">{{ __('messages.users.resident') }}</span>
                @break
            @default
                <span class="px-3 py-1 bg-surface-container-low text-on-surface-variant rounded-full text-[11px] font-bold uppercase tracking-wider">{{ $user->role }}</span>
        @endswitch
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-secondary">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.users.condominium') }}</p>
        <h3 class="font-headline-md text-headline-md text-secondary">{{ $user->condominium->name ?? '—' }}</h3>
    </div>
    <div class="bg-white p-lg rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] border-l-4 border-outline">
        <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.common.status') }}</p>
        @switch($user->status)
            @case('active')
                <span class="px-3 py-1 bg-[#E3FCEF] text-[#006644] rounded-full text-[11px] font-bold uppercase tracking-wider">{{ __('messages.common.active') }}</span>
                @break
            @case('inactive')
                <span class="px-3 py-1 bg-[#FFEBE6] text-[#BF2600] rounded-full text-[11px] font-bold uppercase tracking-wider">{{ __('messages.common.inactive') }}</span>
                @break
            @default
                <span class="px-3 py-1 bg-surface-container-low text-on-surface-variant rounded-full text-[11px] font-bold uppercase tracking-wider">{{ $user->status }}</span>
        @endswitch
    </div>
</div>

<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] p-lg mb-xl">
    <h4 class="font-headline-md text-headline-md mb-lg">{{ __('messages.common.details') }}</h4>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
        <div>
            <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.users.name') }}</p>
            <p class="text-on-surface">{{ $user->name }}</p>
        </div>
        <div>
            <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.users.email') }}</p>
            <p class="text-on-surface">{{ $user->email }}</p>
        </div>
        <div>
            <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.users.phone') }}</p>
            <p class="text-on-surface">{{ $user->phone ?? '—' }}</p>
        </div>
        <div>
            <p class="text-on-surface-variant font-label-caps text-label-caps mb-xs">{{ __('messages.users.language') }}</p>
            <p class="text-on-surface">{{ $user->language === 'es' ? __('messages.condominiums.spanish') : __('messages.condominiums.english') }}</p>
        </div>
    </div>
</div>

@if($user->relationLoaded('apartments') && $user->apartments->count() > 0)
<div class="bg-white rounded-xl shadow-[0px_2px_4px_rgba(0,0,0,0.05)] overflow-hidden">
    <div class="p-lg border-b border-surface-container-low">
        <h4 class="font-headline-md text-headline-md">{{ __('messages.apartments.title') }}</h4>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-low/50">
                <tr>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.apartments.number') }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.apartments.owner_name') }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.apartments.condominium') }}</th>
                    <th class="px-lg py-md text-label-caps font-label-caps text-on-surface-variant">{{ __('messages.common.status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-container-low">
                @foreach($user->apartments as $apt)
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-lg py-md font-bold text-on-surface">{{ $apt->number }}</td>
                        <td class="px-lg py-md text-on-surface-variant">{{ $apt->owner_name ?? '—' }}</td>
                        <td class="px-lg py-md text-on-surface-variant">{{ $apt->condominium->name ?? '—' }}</td>
                        <td class="px-lg py-md">
                            @switch($apt->status)
                                @case('active')
                                    <span class="px-3 py-1 bg-[#E3FCEF] text-[#006644] rounded-full text-[11px] font-bold uppercase tracking-wider">{{ __('messages.common.active') }}</span>
                                    @break
                                @case('inactive')
                                    <span class="px-3 py-1 bg-[#FFEBE6] text-[#BF2600] rounded-full text-[11px] font-bold uppercase tracking-wider">{{ __('messages.common.inactive') }}</span>
                                    @break
                                @default
                                    <span class="px-3 py-1 bg-surface-container-low text-on-surface-variant rounded-full text-[11px] font-bold uppercase tracking-wider">{{ $apt->status }}</span>
                            @endswitch
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection