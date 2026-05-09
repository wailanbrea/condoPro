<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\NotificationService;

class PaymentObserver
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function updated(Payment $payment): void
    {
        if ($payment->isDirty('status')) {
            if ($payment->status === 'confirmed') {
                $payment->load('user', 'condominium');
                $residentName = $payment->user?->name ?? 'Residente';

                $this->notificationService->notifyUser(
                    $payment->user_id,
                    $payment->condominium_id,
                    'payment',
                    'Pago confirmado',
                    "Se ha confirmado su pago de RD$" . number_format($payment->amount, 2) . ".",
                    ['payment_id' => $payment->id]
                );
            }

            if ($payment->status === 'rejected') {
                $this->notificationService->notifyUser(
                    $payment->user_id,
                    $payment->condominium_id,
                    'warning',
                    'Pago rechazado',
                    "Su pago de RD$" . number_format($payment->amount, 2) . " ha sido rechazado. {$payment->rejection_reason}",
                    ['payment_id' => $payment->id]
                );
            }
        }
    }
}