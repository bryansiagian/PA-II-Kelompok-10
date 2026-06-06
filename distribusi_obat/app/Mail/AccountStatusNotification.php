<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountStatusNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $status // 'approved' | 'rejected'
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->status === 'approved'
            ? 'Akun E-Pharma Anda Telah Disetujui'
            : 'Informasi Pendaftaran Akun E-Pharma';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.account_status');
    }
}
