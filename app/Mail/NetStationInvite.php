<?php
namespace App\Mail;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NetStationInvite extends Mailable
{
    public function __construct(
        public string $toEmail,
        public string $callsign,
        public string $name,
        public string $groupName,
        public string $inviteUrl,
        public ?string $adminName = null,
    ) {}

    public function envelope(): Envelope {
        return new Envelope(subject: 'Join ' . $this->groupName . ' — Invitation for ' . $this->callsign);
    }

    public function content(): Content {
        return new Content(view: 'emails.net-station-invite');
    }
}
