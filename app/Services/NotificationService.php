<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    public function notifyCondominium(int $condominiumId, string $type, string $title, string $body, ?array $data = null): void
    {
        $users = User::where('condominium_id', $condominiumId)
            ->where('status', 'active')
            ->get();

        foreach ($users as $user) {
            Notification::create([
                'condominium_id' => $condominiumId,
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'data' => $data,
            ]);
        }
    }

    public function notifyUser(int $userId, int $condominiumId, string $type, string $title, string $body, ?array $data = null): void
    {
        Notification::create([
            'condominium_id' => $condominiumId,
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);
    }

    public function notifyResidents(int $condominiumId, string $type, string $title, string $body, ?array $data = null): void
    {
        $residents = User::where('condominium_id', $condominiumId)
            ->where('role', 'resident')
            ->where('status', 'active')
            ->get();

        foreach ($residents as $resident) {
            Notification::create([
                'condominium_id' => $condominiumId,
                'user_id' => $resident->id,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'data' => $data,
            ]);
        }
    }

    public function notifyAdmins(int $condominiumId, string $type, string $title, string $body, ?array $data = null): void
    {
        $admins = User::where(function ($q) use ($condominiumId) {
            $q->where('role', 'super_admin')
                ->orWhere(function ($q2) use ($condominiumId) {
                    $q2->where('role', 'admin')->where('condominium_id', $condominiumId);
                });
        })->where('status', 'active')->get();

        foreach ($admins as $admin) {
            Notification::create([
                'condominium_id' => $condominiumId,
                'user_id' => $admin->id,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'data' => $data,
            ]);
        }
    }
}