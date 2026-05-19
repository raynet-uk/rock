<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminNotification extends Model
{
    protected $fillable = [
        'title', 'body', 'priority', 'sent_by', 'sent_to_all',
    ];

    protected $casts = [
        'sent_to_all' => 'boolean',
        'priority'    => 'integer',
    ];

    public static function priorityConfig(): array
    {
        return [
            1 => ['label' => 'Emergency',   'colour' => '#C8102E', 'bg' => '#fef2f2', 'text' => '#7f1d1d', 'icon' => '🚨'],
            2 => ['label' => 'Urgent',      'colour' => '#ea580c', 'bg' => '#fff7ed', 'text' => '#7c2d12', 'icon' => '⚡'],
            3 => ['label' => 'Operational', 'colour' => '#f59e0b', 'bg' => '#fffbeb', 'text' => '#78350f', 'icon' => '⚙️'],
            4 => ['label' => 'Advisory',    'colour' => '#0288d1', 'bg' => '#e0f2fe', 'text' => '#0c4a6e', 'icon' => 'ℹ️'],
            5 => ['label' => 'Routine',     'colour' => '#22c55e', 'bg' => '#f0fdf4', 'text' => '#14532d', 'icon' => '📋'],
        ];
    }

    public function priorityMeta(): array
    {
        return static::priorityConfig()[$this->priority] ?? static::priorityConfig()[1];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(AdminNotificationRecipient::class, 'notification_id');
    }

    public function activeRecipients(): HasMany
    {
        return $this->hasMany(AdminNotificationRecipient::class, 'notification_id')
            ->whereNull('removed_at');
    }
}