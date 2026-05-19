<?php
namespace App\Notifications;
use App\Models\Resource;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResourceApprovedNotification extends Notification {
    use Queueable;

    public function __construct(public Resource $resource) {}

    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage {
        return (new MailMessage)
            ->subject('Your file has been approved — Liverpool RAYNET')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your file **' . $this->resource->title . '** has been approved and is now live in the resources library.')
            ->action('View Resources', url('/resources'))
            ->line('Thank you for contributing to the RAYNET resources library.');
    }
}
