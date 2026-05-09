<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $notifications = Notification::forUser($user->id)
            ->when($user->role === 'admin', fn($q) => $q->where('condominium_id', $user->condominium_id))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function markAsRead(Notification $notification)
    {
        $notification->markAsRead();
        return back();
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        Notification::forUser($user->id)
            ->when($user->role === 'admin', fn($q) => $q->where('condominium_id', $user->condominium_id))
            ->unread()
            ->update(['read_at' => now()]);

        return back()->with('success', __('messages.common.save') . '!');
    }

    public function unreadCount()
    {
        $user = Auth::user();
        return Notification::forUser($user->id)
            ->when($user->role === 'admin', fn($q) => $q->where('condominium_id', $user->condominium_id))
            ->unread()
            ->count();
    }
}