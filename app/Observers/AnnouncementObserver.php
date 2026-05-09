<?php

namespace App\Observers;

use App\Models\Announcement;
use App\Services\NotificationService;

class AnnouncementObserver
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function created(Announcement $announcement): void
    {
        $this->notificationService->notifyCondominium(
            $announcement->condominium_id,
            'announcement',
            $announcement->title,
            $announcement->body,
            ['announcement_id' => $announcement->id, 'priority' => $announcement->priority]
        );
    }

    public function updated(Announcement $announcement): void
    {
        if ($announcement->isDirty('is_pinned') && $announcement->is_pinned) {
            $this->notificationService->notifyCondominium(
                $announcement->condominium_id,
                'announcement',
                'Aviso fijado: ' . $announcement->title,
                $announcement->body,
                ['announcement_id' => $announcement->id]
            );
        }
    }
}