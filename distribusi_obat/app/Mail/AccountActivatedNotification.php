<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountActivatedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $plainPassword
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Akun E-Pharma Anda Telah Diaktifkan');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.account_activated');
    }
}
