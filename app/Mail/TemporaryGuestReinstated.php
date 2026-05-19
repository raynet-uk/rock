<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Password;

class TemporaryGuestReinstated extends Mailable
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
            subject: 'Your Guest Access Has Been Reinstated — ' . \App\Helpers\RaynetSetting::groupName(),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.temporary-guest-reinstated',
        );
    }
}
