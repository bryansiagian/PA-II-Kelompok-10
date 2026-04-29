<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public $userName, public $otpCode) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->otpCode . ' adalah kode verifikasi E-Pharma Anda',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp_notification',
        );
    }
}
