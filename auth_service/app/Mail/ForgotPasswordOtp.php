<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ForgotPasswordOtp extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $userName, public string $otpCode) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Reset Password E-Pharma');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.forgot_password_otp');
    }
}
