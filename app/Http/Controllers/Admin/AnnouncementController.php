<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Condominium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $announcements = Announcement::with('condominium', 'creator')
            ->when($user->role === 'admin', fn($q) => $q->where('condominium_id', $user->condominium_id))
            ->orderBy('is_pinned', 'desc')
            ->orderBy('published_at', 'desc')
            ->paginate(15);

        return view('admin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        $condominiums = $this->getCondominiumsForSelect();
        return view('admin.announcements.create', compact('condominiums'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'condominium_id' => 'required|exists:condominiums,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'is_pinned' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
        ]);

        $this->authorizeCondo($validated['condominium_id']);

        $validated['created_by'] = Auth::id();
        $validated['is_pinned'] = $validated['is_pinned'] ?? false;
        $validated['published_at'] = $validated['published_at'] ?? now();

        Announcement::create($validated);

        return redirect()->route('announcements.index')
            ->with('success', __('messages.common.save') . '!');
    }

    public function show(Announcement $announcement)
    {
        $this->authorizeCondo($announcement->condominium_id);
        $announcement->load('condominium', 'creator', 'readers');
        return view('admin.announcements.show', compact('announcement'));
    }

    public function edit(Announcement $announcement)
    {
        $this->authorizeCondo($announcement->condominium_id);
        $condominiums = $this->getCondominiumsForSelect();
        return view('admin.announcements.edit', compact('announcement', 'condominiums'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $this->authorizeCondo($announcement->condominium_id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'is_pinned' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
        ]);

        $validated['is_pinned'] = $validated['is_pinned'] ?? false;
        $announcement->update($validated);

        return redirect()->route('announcements.index')
            ->with('success', __('messages.common.save') . '!');
    }

    public function destroy(Announcement $announcement)
    {
        $this->authorizeCondo($announcement->condominium_id);
        $announcement->delete();

        return redirect()->route('announcements.index')
            ->with('success', __('messages.common.delete') . '!');
    }

    private function authorizeCondo(int $condominiumId): void
    {
        $user = Auth::user();
        if ($user->role === 'admin' && $condominiumId !== $user->condominium_id) {
            abort(403, __('messages.auth.unauthorized'));
        }
    }

    private function getCondominiumsForSelect()
    {
        $user = Auth::user();
        if ($user->role === 'super_admin') {
            return Condominium::orderBy('name')->pluck('name', 'id');
        }
        return Condominium::where('id', $user->condominium_id)->pluck('name', 'id');
    }
}