<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\AdminNotificationRecipient;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class NotificationAdminController extends Controller
{
    public function index()
    {
        $notifications = AdminNotification::with([
            'sender',
            'recipients.user',
        ])
        ->orderByDesc('created_at')
        ->paginate(20);

        $priorityConfig = AdminNotification::priorityConfig();
        $totalUsers     = User::where('registration_pending', false)->count();

        return view('admin.notifications.index', compact(
            'notifications', 'priorityConfig', 'totalUsers'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'body'       => ['nullable', 'string', 'max:2000'],
            'priority'   => ['required', 'integer', 'min:1', 'max:5'],
            'send_to'    => ['required', 'in:all,selected'],
            'user_ids'   => ['required_if:send_to,selected', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $notification = AdminNotification::create([
            'title'       => $data['title'],
            'body'        => $data['body'] ?? null,
            'priority'    => $data['priority'],
            'sent_by'     => auth()->id(),
            'sent_to_all' => $data['send_to'] === 'all',
        ]);

        if ($data['send_to'] === 'all') {
            $userIds = User::where('registration_pending', false)->pluck('id');
        } else {
            $userIds = collect($data['user_ids']);
        }

        $rows = $userIds->map(fn ($uid) => [
            'notification_id' => $notification->id,
            'user_id'         => $uid,
            'email_token'     => $data['priority'] <= 3
                ? bin2hex(random_bytes(32))
                : null,
            'created_at'      => now(),
            'updated_at'      => now(),
        ])->values()->all();

        AdminNotificationRecipient::insert($rows);

        // ©¤©¤ Email + Telegram for priorities 1¨C3 ©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤
        if ($data['priority'] <= 3) {
            $recipients = AdminNotificationRecipient::with('user')
                ->where('notification_id', $notification->id)
                ->get();

            // ©¤©¤ Email ©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤
            foreach ($recipients as $recipient) {
                if (! $recipient->user) continue;
                try {
                    $recipient->user->notify(
                        new \App\Notifications\AdminNotificationEmail($notification, $recipient->email_token)
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning(
                        "Failed to send notification email to user {$recipient->user_id}: " . $e->getMessage()
                    );
                }
            }

            // ©¤©¤ Telegram ©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤©¤
            try {
                $telegram     = new TelegramService();
                $priorityMeta = $notification->priorityMeta();
                $message      = TelegramService::formatNotification(
                    $notification->priority,
                    $notification->title,
                    $notification->body,
                    \App\Helpers\RaynetSetting::groupName(),
                    $priorityMeta['label'],
                    $priorityMeta['icon'],
                );

                // 1. Send to group channel
                $telegram->sendGroupNotification($message);

                // 2. Send DMs to individual recipients who have a telegram_chat_id
                foreach ($recipients as $recipient) {
                    if (! $recipient->user || empty($recipient->user->telegram_chat_id)) continue;
                    $telegram->sendUserNotification($recipient->user->telegram_chat_id, $message);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning(
                    'Failed to send Telegram notification: ' . $e->getMessage()
                );
            }
        }

        return redirect()->route('admin.notifications.index')
            ->with('status', "Notification sent to {$userIds->count()} member(s)" .
                ($data['priority'] <= 3 ? ' ¡¤ Email & Telegram sent to all recipients' : '') . '.');
    }

    public function destroy(AdminNotification $notification)
    {
        $notificationId = $notification->id;
        $wasHqBroadcast = is_null($notification->sent_by);

        $notification->delete();

        // If this was an HQ broadcast, also delete it on the Command Centre
        if ($wasHqBroadcast) {
            try {
                $licenceKey = \App\Models\Setting::get('cms_licence_key', '');
                if ($licenceKey) {
                    \Illuminate\Support\Facades\Http::timeout(10)
                        ->withHeaders(['X-CMS-Licence' => $licenceKey])
                        ->post('https://command.nathandillon.co.uk/api/delete-notification', [
                            'notification_id' => $notificationId,
                        ]);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to delete notification on Command Centre: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.notifications.index')
            ->with('status', 'Notification deleted.');
    }

    public function removeRecipient(AdminNotification $notification, User $user)
    {
        AdminNotificationRecipient::where('notification_id', $notification->id)
            ->where('user_id', $user->id)
            ->update(['removed_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function userSearch(Request $request)
    {
        $q = $request->get('q', '');

        $users = User::where('registration_pending', false)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%")
                      ->orWhere('callsign', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name', 'email', 'callsign']);

        return response()->json($users);
    }
}