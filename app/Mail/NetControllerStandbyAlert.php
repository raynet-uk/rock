<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NetControllerStandbyAlert extends Mailable
{
    public function __construct(
        public string  $controllerName,
        public string  $controllerCallsign,
        public string  $netCallsign,
        public string  $frequency,
        public string  $slotFrom,
        public string  $slotTo,
        public string  $groupName,
        public string  $portalUrl,
        public ?string $prevControllerCallsign = null,
    ) {}

    public function envelope(): Envelope {
        return new Envelope(
            subject: '⏳ Your Net Control Slot Starts in 15 Minutes — ' . $this->netCallsign
        );
    }

    public function content(): Content {
        return new Content(view: 'emails.net-controller-standby-alert');
    }
}
