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
        
        // Get unread IDs before marking as read (to show "New" badge on first view)
        $unreadIds = Notification::forUser($user->id)
            ->when($user->role === 'admin', fn($q) => $q->where('condominium_id', $user->condominium_id))
            ->unread()
            ->pluck('id')
            ->toArray();
        
        $notifications = Notification::forUser($user->id)
            ->when($user->role === 'admin', fn($q) => $q->where('condominium_id', $user->condominium_id))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Mark all as read after fetching (counter will reset on next page load)
        if (count($unreadIds) > 0) {
            Notification::whereIn('id', $unreadIds)->update(['read_at' => now()]);
        }

        return view('admin.notifications.index', compact('notifications', 'unreadIds'));
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

    public function destroy(Notification $notification)
    {
        $user = Auth::user();
        
        // Verify the notification belongs to the user
        if ($notification->user_id !== $user->id && $notification->user_id !== null) {
            abort(403, 'Unauthorized');
        }
        
        // For admin, also check condominium
        if ($user->role === 'admin' && $notification->condominium_id !== $user->condominium_id) {
            abort(403, 'Unauthorized');
        }
        
        $notification->delete();
        
        return back()->with('success', app()->getLocale() === 'es' ? 'Notificación eliminada' : 'Notification deleted');
    }

    public function clearAll()
    {
        $user = Auth::user();
        
        $query = Notification::forUser($user->id);
        
        if ($user->role === 'admin') {
            $query->where('condominium_id', $user->condominium_id);
        }
        
        $count = $query->count();
        $query->delete();
        
        $message = app()->getLocale() === 'es' 
            ? "{$count} notificaciones eliminadas" 
            : "{$count} notifications deleted";
        
        return back()->with('success', $message);
    }
}