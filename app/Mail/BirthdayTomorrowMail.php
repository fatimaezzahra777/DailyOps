<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BirthdayTomorrowMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $birthdayUser,
        public User $recipient,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "'s birthday is tomorrow",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.birthday-tomorrow',
        );
    }
}
