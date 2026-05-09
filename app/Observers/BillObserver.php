<?php

namespace App\Observers;

use App\Models\MonthlyBill;
use App\Services\NotificationService;

class BillObserver
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function created(MonthlyBill $bill): void
    {
        $bill->load('apartment', 'condominium');
        $apartment = $bill->apartment;

        if ($apartment && $apartment->users->count() > 0) {
            foreach ($apartment->users as $resident) {
                $this->notificationService->notifyUser(
                    $resident->id,
                    $bill->condominium_id,
                    'bill',
                    'Nueva factura generada',
                    "Se ha generado la factura #{$bill->id} para el apartamento {$apartment->number} por un total de RD$" . number_format($bill->total, 2),
                    ['bill_id' => $bill->id, 'apartment' => $apartment->number]
                );
            }
        }
    }

    public function updated(MonthlyBill $bill): void
    {
        if ($bill->isDirty('status') && $bill->status === 'overdue') {
            $bill->load('apartment');
            $apartment = $bill->apartment;

            if ($apartment && $apartment->users->count() > 0) {
                foreach ($apartment->users as $resident) {
                    $this->notificationService->notifyUser(
                        $resident->id,
                        $bill->condominium_id,
                        'warning',
                        'Factura vencida',
                        "La factura #{$bill->id} del apartamento {$apartment->number} ha vencido.",
                        ['bill_id' => $bill->id]
                    );
                }
            }
        }
    }
}