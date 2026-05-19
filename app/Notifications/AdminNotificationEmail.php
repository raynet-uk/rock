<?php

namespace App\Notifications;

use App\Models\AdminNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AdminNotificationEmail extends Notification
{
    use Queueable;

    public function __construct(
        public AdminNotification $notification,
        public ?string $emailToken = null
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $cfg    = $this->notification->priorityMeta();
        $priority = $this->notification->priority;

        $subjectPrefix = match($priority) {
            1 => '[EMERGENCY]',
            2 => '[URGENT]',
            3 => '[OPERATIONAL]',
            default => '[RAYNET]',
        };

        $groupName = \App\Helpers\RaynetSetting::groupName();

        return (new MailMessage)
            ->subject("{$subjectPrefix} {$this->notification->title} — {$groupName}")
            ->view('emails.admin-notification', [
                'notif'      => $this->notification,
                'cfg'        => $cfg,
                'user'       => $notifiable,
                'emailToken' => $this->emailToken,
            ]);
    }
}