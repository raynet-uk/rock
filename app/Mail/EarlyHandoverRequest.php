<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class EarlyHandoverRequest extends Mailable
{
    public function __construct(
        public string  $requesterName,
        public string  $requesterCallsign,
        public string  $requesterSlotFrom,
        public string  $requesterSlotTo,
        public string  $netCallsign,
        public string  $frequency,
        public string  $groupName,
        public string  $acceptUrl,
        public bool    $isFallback = false,
    ) {}

    public function envelope(): Envelope {
        return new Envelope(
            subject: '🚨 Early Handover Requested — ' . $this->netCallsign
        );
    }

    public function content(): Content {
        return new Content(view: 'emails.early-handover-request');
    }
}
