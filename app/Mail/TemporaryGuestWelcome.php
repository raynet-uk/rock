<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Password;

class TemporaryGuestWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public string $resetUrl;

    public function __construct(public User $user)
    {
        // Generate a password-reset token so they can set their own password
        $token = Password::createToken($user);
        $this->resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ], false));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Temporary Guest Access — ' . \App\Helpers\RaynetSetting::groupName(),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.temporary-guest-welcome',
        );
    }
}
