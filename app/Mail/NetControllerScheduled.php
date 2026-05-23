<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NetControllerScheduled extends Mailable
{
    public function __construct(
        public string  $controllerName,
        public string  $controllerCallsign,
        public string  $netCallsign,
        public string  $netName,
        public string  $frequency,
        public string  $slotStart,
        public string  $slotEnd,
        public string  $groupName,
        public ?string $description = null,
        public ?string $announcement = null,
        public ?string $netUrl = null,
    ) {}

    public function envelope(): Envelope {
        return new Envelope(
            subject: 'You have been scheduled as Net Controller — ' . $this->netCallsign
        );
    }

    public function content(): Content {
        return new Content(view: 'emails.net-controller-scheduled');
    }
}
