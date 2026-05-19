<?php
namespace App\Notifications;
use App\Models\Resource;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResourcePendingNotification extends Notification {
    use Queueable;

    public function __construct(public Resource $resource) {}

    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage {
        return (new MailMessage)
            ->subject('Resource pending approval — Liverpool RAYNET')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new file has arrived by email and needs your approval:')
            ->line('**' . $this->resource->title . '** from ' . $this->resource->uploaded_by)
            ->line('Visibility: ' . ucfirst($this->resource->visibility))
            ->action('Review Now', url('/resources'))
            ->line('Please log in and approve or delete the file from the pending queue.');
    }
}
