<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Payment $payment) {}

    public function build(): self
    {
        return $this->subject('Pago Rechazado - CondoPro')
            ->view('emails.payment-rejected')
            ->with([
                'payment' => $this->payment,
                'apartment' => $this->payment->apartment,
                'condominium' => $this->payment->condominium,
            ]);
    }
}