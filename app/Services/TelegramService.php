<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class TelegramService
{
    protected string $token;
    protected string $apiBase;
    public function __construct()
    {
        $this->token   = config('services.telegram.bot_token');
        $this->apiBase = "https://api.telegram.org/bot{$this->token}";
    }
    public function sendMessage(string|int $chatId, string $text, ?int $threadId = null): bool
    {
        try {
            $payload = [
                'chat_id'                  => $chatId,
                'text'                     => $text,
                'parse_mode'               => 'HTML',
                'disable_web_page_preview' => true,
            ];
            if ($threadId) {
                $payload['message_thread_id'] = $threadId;
            }
            $response = Http::timeout(10)->post("{$this->apiBase}/sendMessage", $payload);
            if (! $response->successful()) {
                Log::warning('Telegram sendMessage failed', [
                    'chat_id'   => $chatId,
                    'thread_id' => $threadId,
                    'status'    => $response->status(),
                    'response'  => $response->body(),
                ]);
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            Log::error('Telegram sendMessage exception', [
                'chat_id' => $chatId,
                'error'   => $e->getMessage(),
            ]);
            return false;
        }
    }
    public function sendGroupNotification(string $text): bool
    {
        $raw = \App\Models\Setting::get('telegram_group_chat_ids', config('services.telegram.group_chat_id'));
        if (empty($raw)) {
            Log::warning('Telegram group chat ID not configured.');
            return false;
        }
        $threadId = (int) \App\Models\Setting::get('telegram_group_thread_id', 0) ?: null;
        Log::info('Telegram sendGroupNotification', ['raw' => $raw, 'threadId' => $threadId]);
        $ids = array_filter(array_map('trim', explode(',', $raw)));
        $success = true;
        foreach ($ids as $id) {
            if (!$this->sendMessage($id, $text, $threadId)) {
                $success = false;
            }
        }
        return $success;
    }
    public function sendUserNotification(string|int $chatId, string $text): bool
    {
        return $this->sendMessage($chatId, $text);
    }
    public static function formatNotification(
        int    $priority,
        string $title,
        ?string $body,
        string $groupName,
        string $priorityLabel,
        string $priorityIcon
    ): string {
        $header = match(true) {
            $priority === 1 => "🚨 <b>PRIORITY 1 — IMMEDIATE ACTION</b>",
            $priority === 2 => "⚠️ <b>PRIORITY 2 — URGENT</b>",
            $priority === 3 => "📢 <b>PRIORITY 3 — IMPORTANT</b>",
            default         => "ℹ️ <b>PRIORITY {$priority}</b>",
        };
        $lines = [
            $header,
            "",
            "<b>{$title}</b>",
        ];
        if (! empty($body)) {
            $lines[] = "";
            $lines[] = $body;
        }
        $lines[] = "";
        $lines[] = "━━━━━━━━━━━━━━━━━━━━";
        $lines[] = "📡 <i>{$groupName}</i>";
        $lines[] = "🏷 {$priorityIcon} {$priorityLabel}";
        return implode("\n", $lines);
    }
    public function sendAlertNotification(int $level, ?string $headline = null, ?string $message = null): bool
    {
        $levelData = match($level) {
            1 => ['icon' => '🔴', 'label' => 'ALERT LEVEL 1 — ACTIVE INCIDENT',           'desc' => 'An incident is underway. All operators should activate immediately.',                                                          'bar' => '🔴🔴🔴🔴🔴'],
            2 => ['icon' => '🟠', 'label' => 'ALERT LEVEL 2 — INCIDENT IMMINENT',          'desc' => 'An incident is imminent. Operators should be on standby and ready to deploy.',                                                 'bar' => '🟠🟠🟠🟠⚫'],
            3 => ['icon' => '🟡', 'label' => 'ALERT LEVEL 3 — INCIDENT PROBABLE',          'desc' => 'There is a strong likelihood of a RAYNET activation. Operators should ensure batteries are charged and go-bags ready.',        'bar' => '🟡🟡🟡⚫⚫'],
            4 => ['icon' => '🔵', 'label' => 'ALERT LEVEL 4 — INCIDENT POSSIBLE / TRAINING','desc' => 'An incident is possible, or a training exercise is underway. Monitor channels.',                                              'bar' => '🔵🔵⚫⚫⚫'],
            5 => ['icon' => '🟢', 'label' => 'ALERT LEVEL 5 — NO INCIDENTS',               'desc' => 'No incidents or activations expected. Normal operations.',                                                                     'bar' => '🟢⚫⚫⚫⚫'],
            default => ['icon' => '⚪', 'label' => "ALERT LEVEL {$level}", 'desc' => '', 'bar' => '⚪⚫⚫⚫⚫'],
        };
        $divider   = '━━━━━━━━━━━━━━━━━━━━━━━━━━━';
        $timestamp = now()->setTimezone('Europe/London')->format('d M Y \a\t H:i');
        $body      = !empty($message) ? $message : $levelData['desc'];
        $lines = [
            $levelData['icon'] . ' <b>RAYNET LIVERPOOL — ALERT STATUS CHANGE</b>',
            '',
            $divider,
            '⚡ <b>' . $levelData['label'] . '</b>',
            $divider,
            '',
        ];
        if (!empty($headline)) { $lines[] = '📣 <b>' . $headline . '</b>'; $lines[] = ''; }
        if (!empty($body))     { $lines[] = '<i>' . $body . '</i>';         $lines[] = ''; }
        $lines[] = 'Alert Level: ' . $levelData['bar'];
        $lines[] = '';
        $lines[] = $divider;
        $lines[] = '🕐 <b>' . $timestamp . '</b>';
        $lines[] = '📡 <i>RAYNET Liverpool</i>';
        return $this->sendGroupNotification(implode("\n", $lines));
    }
}
