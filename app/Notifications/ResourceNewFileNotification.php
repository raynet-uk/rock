<?php
namespace App\Notifications;
use App\Models\Resource;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResourceNewFileNotification extends Notification {
    use Queueable;

    public function __construct(public Resource $resource) {}

    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage {
        $vis = $this->resource->visibility === 'members' ? 'members-only' : 'public';
        return (new MailMessage)
            ->subject('New resource added: ' . $this->resource->title . ' — Liverpool RAYNET')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new ' . $vis . ' resource has been added to the **' . ($this->resource->category ?: 'General') . '** category:')
            ->line('**' . $this->resource->title . '**')
            ->line($this->resource->description ?? '')
            ->action('View Resources', url('/resources'))
            ->line('You are receiving this because you follow the ' . ($this->resource->category ?: 'General') . ' category.');
    }
}
