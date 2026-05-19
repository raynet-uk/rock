<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TemporaryGuestConverted extends Mailable
{
    use Queueable, SerializesModels;

    public string $loginUrl;

    public function __construct(public User $user)
    {
        $this->loginUrl = url(route('login'));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Account Access Has Changed — ' . \App\Helpers\RaynetSetting::groupName(),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.temporary-guest-converted',
        );
    }
}
