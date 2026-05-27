<?php
namespace App\Mail;

use App\Models\EventAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamBriefing extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public EventAssignment $assignment,
        public string $customMessage = '',
        public ?string $pdfPath = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Operator Briefing — ' . $this->assignment->event->title
                   . ' · ' . ($this->assignment->event->starts_at?->format('D j M Y') ?? '')
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.operator-briefing');
    }

    public function attachments(): array
    {
        if (!$this->pdfPath || !file_exists($this->pdfPath)) return [];
        return [
            Attachment::fromPath($this->pdfPath)
                ->as('Briefing_' . str_replace(' ', '_', $this->assignment->user->name) . '_' . str_replace(' ', '_', $this->assignment->event->title) . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
