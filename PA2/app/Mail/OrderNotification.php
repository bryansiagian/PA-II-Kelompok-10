<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    // Ubah variabel agar sesuai dengan Model terbaru
    public function __construct(public $productOrder, public $statusLabel) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update Pesanan E-Pharma: #' . $this->productOrder->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_notification',
        );
    }
}
