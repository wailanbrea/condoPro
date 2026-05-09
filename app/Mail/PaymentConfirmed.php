<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Payment $payment) {}

    public function build(): self
    {
        return $this->subject('Pago Confirmado - CondoPro')
            ->view('emails.payment-confirmed')
            ->with([
                'payment' => $this->payment,
                'apartment' => $this->payment->apartment,
                'condominium' => $this->payment->condominium,
            ]);
    }
}