<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TelegramWebhookController extends Controller
{
    protected function getState(int|string $fromId): ?array
    {
        $all = json_decode(\App\Models\Setting::get('telegram_conv_state', '{}'), true) ?? [];
        return $all[(string) $fromId] ?? null;
    }

    protected function setState(int|string $fromId, array $state): void
    {
        $all = json_decode(\App\Models\Setting::get('telegram_conv_state', '{}'), true) ?? [];
        $all[(string) $fromId] = $state;
        \App\Models\Setting::set('telegram_conv_state', json_encode($all));
    }

    protected function clearState(int|string $fromId): void
    {
        $all = json_decode(\App\Models\Setting::get('telegram_conv_state', '{}'), true) ?? [];
        unset($all[(string) $fromId]);
        \App\Models\Setting::set('telegram_conv_state', json_encode($all));
    }

    protected function parseUKDate(string $input): ?Carbon
    {
        $input = trim($input);
        $formats = ['j/n/y', 'j/n/Y', 'd/m/y', 'd/m/Y', 'j-n-y', 'j-n-Y'];
        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $input);
                if ($date) return $date->startOfDay();
            } catch (\Exception $e) {}
        }
        return null;
    }

    protected function isAdmin(int|string $fromId): bool
    {
        $user = DB::table('users')->where('telegram_chat_id', (string) $fromId)->first();
        if (!$user) return false;
        return (bool) ($user->is_admin || $user->is_super_admin);
    }

    protected function notLinkedMessage(int|string $fromId): string
    {
        return "❌ <b>Your Telegram is not linked to a member account.</b>\n\n" .
               "Visit your profile and paste your Chat ID into the Telegram Chat ID field:\n\n" .
               "<code>{$fromId}</code>\n\n" .
               "👉 raynet-liverpool.net/profile";
    }

    public function handle(Request $request)
    {
        $update = $request->all();
        Log::info('Telegram webhook received', $update);

        if (!empty($update['callback_query'])) {
            $this->handleCallback($update['callback_query']);
            return response()->json(['ok' => true]);
        }

        $message = $update['message'] ?? null;
        if (!$message) return response()->json(['ok' => true]);

        if (isset($message['forum_topic_created'])) {
            $threadId  = $message['message_thread_id'] ?? null;
            $topicName = $message['forum_topic_created']['name'] ?? 'Unknown';
            $chatId    = $message['chat']['id'];
            $chatTitle = $message['chat']['title'] ?? 'Unknown Group';
            if ($threadId) {
                $topics = json_decode(\App\Models\Setting::get('telegram_known_topics', '[]'), true) ?? [];
                $key = $chatId . ':' . $threadId;
                $topics[$key] = ['chat_id' => $chatId, 'thread_id' => $threadId, 'name' => $topicName, 'group' => $chatTitle];
                \App\Models\Setting::set('telegram_known_topics', json_encode($topics));
            }
            return response()->json(['ok' => true]);
        }

        if (isset($message['forum_topic_closed']) || isset($message['forum_topic_edited'])) {
            return response()->json(['ok' => true]);
        }

        $chatId    = $message['chat']['id'];
        $chatType  = $message['chat']['type'] ?? 'private';
        $chatTitle = $message['chat']['title'] ?? null;
        $threadId  = $message['message_thread_id'] ?? null;
        $rawText   = trim($message['text'] ?? '');
        $firstName = $message['from']['first_name'] ?? 'there';
        $fromId    = $message['from']['id'] ?? null;
        $text      = preg_replace('/@\w+/', '', $rawText);

        if (!empty($message['is_topic_message']) && $threadId) {
            $topics = json_decode(\App\Models\Setting::get('telegram_known_topics', '[]'), true) ?? [];
            $key = $chatId . ':' . $threadId;
            if (!isset($topics[$key])) {
                $topics[$key] = ['chat_id' => $chatId, 'thread_id' => $threadId, 'name' => 'Topic #' . $threadId, 'group' => $chatTitle ?? 'Unknown'];
                \App\Models\Setting::set('telegram_known_topics', json_encode($topics));
            }
        }

        $state = $this->getState($fromId);

        if ($state && !in_array($text, ['/cancel', '/start'])) {
            $this->handleConversation($chatId, $threadId, $fromId, $text, $state);
            return response()->json(['ok' => true]);
        }

        if ($text === '/cancel') {
            $this->clearState($fromId);
            $this->sendMessage($chatId, $threadId, "❌ Cancelled.", $this->mainMenuKeyboard());
            return response()->json(['ok' => true]);
        }

        // ── Main menu ──────────────────────────────────────────────────────
        if ($text === '/start') {
            $this->sendMessage($chatId, $threadId,
                "👋 <b>Hello {$firstName}!</b>\n\n" .
                "Welcome to the <b>RAYNET Liverpool Bot</b>.\n\n" .
                "Use the buttons below to get started, or type a command directly.",
                $this->mainMenuKeyboard()
            );
            $this->sendMessageWithInline($chatId, $threadId,
                "📋 <b>What would you like to do?</b>",
                [
                    [
                        ['text' => '🚨 Alert Status',   'callback_data' => 'cmd:status'],
                        ['text' => '📅 Events',          'callback_data' => 'cmd:events'],
                    ],
                    [
                        ['text' => '📻 Callsign Lookup', 'callback_data' => 'cmd:callsign'],
                        ['text' => '🆔 My Chat ID',      'callback_data' => 'cmd:myid'],
                    ],
                    [
                        ['text' => '✅ On Call',          'callback_data' => 'cmd:oncall'],
                        ['text' => '🔴 Off Call',         'callback_data' => 'cmd:offcall'],
                    ],
                    [
                        ['text' => '📆 Log Unavailability',  'callback_data' => 'cmd:unavailability'],
                        ['text' => '🗓 My Unavailability',   'callback_data' => 'cmd:myunavailability'],
                    ],
                ]
            );
        }

        elseif ($text === '/topicid') {
            if (!$this->isAdmin($fromId)) {
                $this->sendMessage($chatId, $threadId, "⛔ This command is restricted to administrators.");
            } elseif ($threadId) {
                $this->sendMessage($chatId, $threadId,
                    "📌 <b>Topic Thread ID</b>\n\n<code>{$threadId}</code>\n\nPaste into <b>Admin → Settings → Telegram → Topic Thread ID</b>."
                );
            } else {
                $this->sendMessage($chatId, null, "⚠️ Use this command inside a topic, not in General.");
            }
        }

        elseif ($text === '/id' || $text === '/getgroupid') {
            if ($chatType === 'private') {
                $this->sendMessage($chatId, $threadId,
                    "🆔 <b>Your Chat ID</b>\n\n<code>{$chatId}</code>\n\n" .
                    "Paste this into your profile:\n👉 raynet-liverpool.net/profile"
                );
            } elseif (!$this->isAdmin($fromId)) {
                $this->sendMessage($chatId, $threadId, "⛔ This command is restricted to administrators.");
            } else {
                if ($threadId) {
                    $topics = json_decode(\App\Models\Setting::get('telegram_known_topics', '[]'), true) ?? [];
                    $key = $chatId . ':' . $threadId;
                    $existingName = $topics[$key]['name'] ?? 'Topic #' . $threadId;
                    $topics[$key] = ['chat_id' => $chatId, 'thread_id' => $threadId, 'name' => $existingName, 'group' => $chatTitle];
                    \App\Models\Setting::set('telegram_known_topics', json_encode($topics));
                }
                $this->sendMessage($chatId, $threadId,
                    "🆔 <b>Group Chat ID</b>\n\n<code>{$chatId}</code>\n\n" .
                    "Group: <b>{$chatTitle}</b>\n" .
                    "Thread ID: <code>" . ($threadId ?? 'General') . "</code>"
                );
            }
        }

        elseif ($text === '/status') {
            if (!$this->userCan($fromId, 'status')) { $this->denyMessage($chatId, $threadId); } else
            $this->sendStatus($chatId, $threadId);
        }

        elseif ($text === '/events') {
            if (!$this->userCan($fromId, 'events')) { $this->denyMessage($chatId, $threadId); } else
            $this->sendEvents($chatId, $threadId);
        }

        elseif ($text === '/nextevent') {
            if (!$this->userCan($fromId, 'nextevent')) { $this->denyMessage($chatId, $threadId); } else
            $this->sendNextEvent($chatId, $threadId);
        }

        elseif ($text === '/callsign') {
            if (!$this->userCan($fromId, 'callsign')) { $this->denyMessage($chatId, $threadId); return response()->json(['ok' => true]); }
            $this->setState($fromId, ['step' => 'callsign_input', 'chat_id' => $chatId, 'thread_id' => $threadId]);
            $this->sendMessage($chatId, $threadId,
                "📻 <b>Callsign Lookup</b>\n\nType the callsign you want to look up:\n\n<i>e.g. G4BDS</i>\n\nSend /cancel to abort.",
                ['remove_keyboard' => true]
            );
        }

        elseif (str_starts_with($text, '/callsign ')) {
            if (!$this->userCan($fromId, 'callsign')) { $this->denyMessage($chatId, $threadId); return response()->json(['ok' => true]); }
            $callsign = strtoupper(trim(substr($text, 10)));
            $this->lookupCallsign($chatId, $threadId, $callsign);
        }

        elseif ($text === '/oncall') {
            if (!$this->userCan($fromId, 'oncall')) { $this->denyMessage($chatId, $threadId); } else
            $this->setOnCall($chatId, $threadId, $fromId, true);
        }

        elseif ($text === '/offcall') {
            if (!$this->userCan($fromId, 'offcall')) { $this->denyMessage($chatId, $threadId); } else
            $this->setOnCall($chatId, $threadId, $fromId, false);
        }

        elseif ($text === '/unavailability') {
            if (!$this->userCan($fromId, 'unavailability')) { $this->denyMessage($chatId, $threadId); } else
            $this->startUnavailability($chatId, $threadId, $fromId);
        }

        elseif ($text === '/myunavailability') {
            if (!$this->userCan($fromId, 'myunavailability')) { $this->denyMessage($chatId, $threadId); } else
            $this->showMyUnavailability($chatId, $threadId, $fromId);
        }

        elseif ($text === '📋 Get My Chat ID') {
            $this->sendMessage($chatId, $threadId,
                "🆔 <b>Your Chat ID</b>\n\n<code>{$chatId}</code>\n\nPaste into your profile:\n👉 raynet-liverpool.net/profile",
                $this->mainMenuKeyboard()
            );
        }

        elseif ($text === 'ℹ️ About') {
            $d = '━━━━━━━━━━━━━━━━━━━━━━━━━━━';
            $this->sendMessage($chatId, $threadId,
                "📡 <b>RAYNET Liverpool Bot</b>\n\n" .
                "{$d}\n\n" .
                "🤖 <b>What I do:</b>\n" .
                "I connect the RAYNET Liverpool Members Portal to Telegram — real-time alert notifications, operator availability, callsign lookups, events and unavailability management, all without leaving Telegram.\n\n" .
                "{$d}\n\n" .
                "📋 <b>Commands:</b>\n" .
                "🚨 /status — Current alert level\n" .
                "📅 /events — Upcoming events\n" .
                "📅 /nextevent — Next event\n" .
                "📻 /callsign — Callsign lookup\n" .
                "✅ /oncall — Mark yourself on call\n" .
                "🔴 /offcall — Mark yourself off call\n" .
                "📆 /unavailability — Log unavailable dates\n" .
                "🗓 /myunavailability — View and remove entries\n" .
                "🆔 /id — Get your Chat ID\n" .
                "❌ /cancel — Cancel current action\n\n" .
                "{$d}\n\n" .
                "🛠 <b>Built by:</b> Nathan Dillon — <code>M7NDN</code>\n" .
                "📡 <b>For:</b> Liverpool RAYNET · Group 10/ME/179\n" .
                "🌐 raynet-liverpool.net\n\n" .
                "{$d}",
                $this->mainMenuKeyboard()
            );
        }

        elseif ($text === '🔕 Help') {
            $this->sendMessage($chatId, $threadId,
                "❓ <b>Help</b>\n\n" .
                "To link your Telegram to your member account:\n\n" .
                "1. Send /id to get your Chat ID\n" .
                "2. Log into <b>raynet-liverpool.net</b>\n" .
                "3. Go to <b>Profile</b> and paste your Chat ID\n" .
                "4. Save — commands like /oncall will then work\n\n" .
                "Portal: 👉 raynet-liverpool.net",
                $this->mainMenuKeyboard()
            );
        }

        return response()->json(['ok' => true]);
    }

    // ── Callback handler ───────────────────────────────────────────────────

    protected function handleCallback(array $callbackQuery): void
    {
        $callbackId = $callbackQuery['id'];
        $fromId     = $callbackQuery['from']['id'];
        $firstName  = $callbackQuery['from']['first_name'] ?? 'there';
        $data       = $callbackQuery['data'] ?? '';
        $chatId     = $callbackQuery['message']['chat']['id'];
        $messageId  = $callbackQuery['message']['message_id'];
        $threadId   = $callbackQuery['message']['message_thread_id'] ?? null;

        $this->answerCallback($callbackId);

        // Main menu button commands
        if (str_starts_with($data, 'cmd:')) {
            $cmd = str_replace('cmd:', '', $data);
            if (!$this->userCan($fromId, $cmd)) {
        $this->answerCallback($callbackId, '⛔ You don\'t have permission to use this.');
        return;
    }
            if ($cmd === 'status') {
                $this->sendStatus($chatId, $threadId);
            } elseif ($cmd === 'events') {
                $this->sendEvents($chatId, $threadId);
            } elseif ($cmd === 'nextevent') {
                $this->sendNextEvent($chatId, $threadId);
            } elseif ($cmd === 'myid') {
                $this->sendMessage($chatId, $threadId,
                    "🆔 <b>Your Chat ID</b>\n\n<code>{$fromId}</code>\n\nPaste into your profile:\n👉 raynet-liverpool.net/profile"
                );
            } elseif ($cmd === 'callsign') {
                $this->setState($fromId, ['step' => 'callsign_input', 'chat_id' => $chatId, 'thread_id' => $threadId]);
                $this->sendMessage($chatId, $threadId,
                    "📻 <b>Callsign Lookup</b>\n\nType the callsign:\n\n<i>e.g. G4BDS</i>\n\nSend /cancel to abort.",
                    ['remove_keyboard' => true]
                );
            } elseif ($cmd === 'oncall') {
                $this->setOnCall($chatId, $threadId, $fromId, true);
            } elseif ($cmd === 'offcall') {
                $this->setOnCall($chatId, $threadId, $fromId, false);
            } elseif ($cmd === 'unavailability') {
                $this->startUnavailability($chatId, $threadId, $fromId);
            } elseif ($cmd === 'myunavailability') {
                $this->showMyUnavailability($chatId, $threadId, $fromId);
            }
        }

        // Delete unavailability entry
        if (str_starts_with($data, 'del_unavail:')) {
            $periodId = (int) str_replace('del_unavail:', '', $data);
            $user     = DB::table('users')->where('telegram_chat_id', (string) $fromId)->first();

            if (!$user) {
                $this->answerCallback($callbackId, 'Not authorised.');
                return;
            }

            $period = DB::table('member_availabilities')
                ->where('id', $periodId)
                ->where('user_id', $user->id)
                ->first();

            if (!$period) {
                $this->editMessage($chatId, $messageId, "⚠️ Entry not found or already removed.");
                return;
            }

            DB::table('member_availabilities')->where('id', $periodId)->delete();

            $from  = Carbon::parse($period->from_date)->format('d/m/Y');
            $to    = Carbon::parse($period->to_date)->format('d/m/Y');
            $range = $from === $to ? $from : "{$from} to {$to}";
            $this->editMessage($chatId, $messageId, "🗑 <b>Removed:</b> {$range}" . ($period->reason ? " — {$period->reason}" : ""));
        }
    }

    // ── Conversation handler ───────────────────────────────────────────────

    protected function handleConversation(int|string $chatId, ?int $threadId, int|string $fromId, string $text, array $state): void
    {
        $step = $state['step'] ?? null;

        if ($step === 'callsign_input') {
            $this->clearState($fromId);
            $this->lookupCallsign($chatId, $threadId, strtoupper(trim($text)));

        } elseif ($step === 'from_date') {
            $date = $this->parseUKDate($text);
            if (!$date) {
                $this->sendMessage($chatId, $threadId, "⚠️ Could not read that date. Use UK format e.g. <code>1/5/26</code>");
                return;
            }
            $state['from_date']         = $date->format('Y-m-d');
            $state['from_date_display'] = $date->format('d/m/Y');
            $state['step']              = 'to_date';
            $this->setState($fromId, $state);
            $this->sendMessage($chatId, $threadId,
                "📅 <b>Step 2 of 3 — End Date</b>\n\nFrom: <b>{$state['from_date_display']}</b>\n\nWhat is the <b>last day</b>?\nType a date or tap <b>Same day</b>.",
                ['keyboard' => [[['text' => '📅 Same day']]], 'resize_keyboard' => true, 'one_time_keyboard' => true]
            );

        } elseif ($step === 'to_date') {
            if ($text === '📅 Same day') {
                $state['to_date']         = $state['from_date'];
                $state['to_date_display'] = $state['from_date_display'];
            } else {
                $date = $this->parseUKDate($text);
                if (!$date) {
                    $this->sendMessage($chatId, $threadId, "⚠️ Could not read that date. Use UK format e.g. <code>5/5/26</code> or tap Same day.");
                    return;
                }
                if ($date->format('Y-m-d') < $state['from_date']) {
                    $this->sendMessage($chatId, $threadId, "⚠️ End date cannot be before start date ({$state['from_date_display']}). Try again.");
                    return;
                }
                $state['to_date']         = $date->format('Y-m-d');
                $state['to_date_display'] = $date->format('d/m/Y');
            }
            $state['step'] = 'reason';
            $this->setState($fromId, $state);
            $this->sendMessage($chatId, $threadId,
                "📅 <b>Step 3 of 3 — Reason</b>\n\nFrom: <b>{$state['from_date_display']}</b>\nTo: <b>{$state['to_date_display']}</b>\n\nWhat is the reason?\nType a reason or tap <b>No reason</b>.",
                ['keyboard' => [[['text' => '✖ No reason']]], 'resize_keyboard' => true, 'one_time_keyboard' => true]
            );

        } elseif ($step === 'reason') {
            $reason   = $text === '✖ No reason' ? null : $text;
            $fromDate = $state['from_date'];
            $toDate   = $state['to_date'];
            $days     = Carbon::parse($fromDate)->diffInDays(Carbon::parse($toDate)) + 1;

            DB::table('member_availabilities')->insert([
                'user_id'    => $state['user_id'],
                'from_date'  => $fromDate,
                'to_date'    => $toDate,
                'reason'     => $reason,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->clearState($fromId);

            $rangeStr = $state['from_date_display'] === $state['to_date_display']
                ? $state['from_date_display']
                : $state['from_date_display'] . ' – ' . $state['to_date_display'];

            $this->sendMessage($chatId, $threadId,
                "✅ <b>Unavailability Logged</b>\n\n" .
                "━━━━━━━━━━━━━━━━━━━━\n" .
                "📅 <b>Period:</b> {$rangeStr}\n" .
                "📆 <b>Days:</b> {$days}\n" .
                ($reason ? "📝 <b>Reason:</b> {$reason}\n" : "") .
                "━━━━━━━━━━━━━━━━━━━━\n\n" .
                "Your Group Controller will see this on the portal.\n" .
                "Use /myunavailability to view or remove entries.",
                $this->mainMenuKeyboard()
            );
        }
    }

    // ── Shared actions ─────────────────────────────────────────────────────

    protected function sendStatus(int|string $chatId, ?int $threadId): void
    {
        $status = DB::table('alert_statuses')->first();
        if (!$status) {
            $this->sendMessage($chatId, $threadId, "ℹ️ No alert status has been set.");
            return;
        }
        $levelData = [
            1 => ['icon' => '🔴', 'label' => 'ALERT LEVEL 1 — ACTIVE INCIDENT',           'bar' => '🔴🔴🔴🔴🔴'],
            2 => ['icon' => '🟠', 'label' => 'ALERT LEVEL 2 — INCIDENT IMMINENT',          'bar' => '🟠🟠🟠🟠⚫'],
            3 => ['icon' => '🟡', 'label' => 'ALERT LEVEL 3 — INCIDENT PROBABLE',          'bar' => '🟡🟡🟡⚫⚫'],
            4 => ['icon' => '🔵', 'label' => 'ALERT LEVEL 4 — INCIDENT POSSIBLE / TRAINING','bar' => '🔵🔵⚫⚫⚫'],
            5 => ['icon' => '🟢', 'label' => 'ALERT LEVEL 5 — NO INCIDENTS',               'bar' => '🟢⚫⚫⚫⚫'],
        ];
        $level = (int) $status->level;
        $info  = $levelData[$level] ?? ['icon' => '⚪', 'label' => "ALERT LEVEL {$level}", 'bar' => '⚪⚫⚫⚫⚫'];
        $divider = '━━━━━━━━━━━━━━━━━━━━━━━━━━━';
        $msg  = "{$info['icon']} <b>RAYNET Liverpool — Alert Status</b>\n\n";
        $msg .= "{$divider}\n";
        $msg .= "⚡ <b>{$info['label']}</b>\n";
        $msg .= "{$divider}\n";
        if (!empty($status->headline)) $msg .= "\n📣 <b>{$status->headline}</b>";
        if (!empty($status->message))  $msg .= "\n<i>{$status->message}</i>";
        $msg .= "\n\nLevel: {$info['bar']}";
        $msg .= "\n\n📡 <i>RAYNET Liverpool</i>";
        $this->sendMessage($chatId, $threadId, $msg);
    }

    protected function sendEvents(int|string $chatId, ?int $threadId): void
    {
        $events = DB::table('events')->where('starts_at', '>=', now())->orderBy('starts_at')->limit(5)->get();
        if ($events->isEmpty()) {
            $this->sendMessage($chatId, $threadId, "📅 No upcoming events found.");
            return;
        }
        $msg = "📅 <b>Upcoming Events</b>\n\n";
        foreach ($events as $event) {
            $start = Carbon::parse($event->starts_at)->setTimezone('Europe/London');
            $msg  .= "━━━━━━━━━━━━━━━━━━━━\n";
            $msg  .= "📌 <b>{$event->title}</b>\n";
            $msg  .= "🕐 " . $start->format('D d M Y \a\t H:i') . "\n";
            if (!empty($event->location)) $msg .= "📍 {$event->location}\n";
        }
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "🌐 <i>raynet-liverpool.net/calendar</i>";
        $this->sendMessage($chatId, $threadId, $msg);
    }

    protected function sendNextEvent(int|string $chatId, ?int $threadId): void
    {
        $event = DB::table('events')->where('starts_at', '>=', now())->orderBy('starts_at')->first();
        if (!$event) {
            $this->sendMessage($chatId, $threadId, "📅 No upcoming events found.");
            return;
        }
        $start = Carbon::parse($event->starts_at)->setTimezone('Europe/London');
        $end   = Carbon::parse($event->ends_at)->setTimezone('Europe/London');
        $diff  = now()->diffForHumans($start, true);
        $msg   = "📅 <b>Next Event</b>\n\n";
        $msg  .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $msg  .= "📌 <b>{$event->title}</b>\n\n";
        $msg  .= "🕐 " . $start->format('D d M Y \a\t H:i') . "\n";
        $msg  .= "🕑 Ends: " . $end->format('H:i') . "\n";
        if (!empty($event->location)) $msg .= "📍 {$event->location}\n";
        $msg  .= "\n⏱ <i>In {$diff}</i>\n";
        $msg  .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $msg  .= "🌐 <i>raynet-liverpool.net/calendar</i>";
        $this->sendMessage($chatId, $threadId, $msg);
    }

protected function lookupCallsign(int|string $chatId, ?int $threadId, string $callsign): void
{
    if (empty($callsign)) {
        $this->sendMessage($chatId, $threadId, "⚠️ Please provide a callsign.");
        return;
    }

    $member  = DB::table('users')->where('callsign', $callsign)->first();
    $qrzData = null;

    try {
        $qrzData = app(\App\Services\QrzService::class)->lookup($callsign);
    } catch (\Exception $e) {
        // QRZ unavailable — continue with local data only
    }

    if (!$member && (!$qrzData || empty($qrzData['name']))) {
        $this->sendMessage($chatId, $threadId, "❌ No information found for callsign <code>{$callsign}</code>.");
        return;
    }

   // ── Raw fields ────────────────────────────────────────────────────────
    $call      = $qrzData['callsign']    ?? $callsign;
    $xref      = $qrzData['xref']        ?? null;
    $aliases   = $qrzData['aliases']     ?? null;
    $fname     = $qrzData['fname']       ?? null;
    $lname     = $qrzData['lname']       ?? null;
    $name      = $qrzData['name']        ?? $member->name ?? null;
    $nameFmt   = $qrzData['name_fmt']    ?? null;
    $nickname  = $qrzData['nickname']    ?? null;
    $attn      = $qrzData['attn']        ?? null;
    $addr1     = $qrzData['address']     ?? null;
    $addr2     = $qrzData['city']        ?? null;
    $state     = $qrzData['state']       ?? null;
    $zip       = $qrzData['zip']         ?? null;
    $county    = $qrzData['county']      ?? null;
    $fips      = $qrzData['fips']        ?? null;
    $country   = $qrzData['country']     ?? null;
    $ccode     = $qrzData['ccode']       ?? null;
    $land      = $qrzData['land']        ?? null;
    $lat       = $qrzData['lat']         ?? null;
    $lon       = $qrzData['lon']         ?? null;
    $grid      = $qrzData['grid']        ?? null;
    $geoloc    = $qrzData['geoloc']      ?? null;
    $dxcc      = $qrzData['dxcc']        ?? null;
    $licence   = $qrzData['licence_code'] ?? null;
    $licenceLabel = $qrzData['licence_class'] ?? $member->licence_class ?? null;
    $codes     = $qrzData['codes']       ?? null;
    $efdate    = $qrzData['efdate']      ?? null;
    $expdate   = $qrzData['expdate']     ?? null;
    $pCall     = $qrzData['p_call']      ?? null;
    $born      = $qrzData['born']        ?? null;
    $cqZone    = $qrzData['cq_zone']     ?? null;
    $ituZone   = $qrzData['itu_zone']    ?? null;
    $timezone  = $qrzData['timezone']    ?? null;
    $gmtOff    = $qrzData['gmt_offset']  ?? null;
    $dst       = $qrzData['dst']         ?? null;
    $msa       = $qrzData['msa']         ?? null;
    $areaCode  = $qrzData['area_code']   ?? null;
    $eqsl      = $qrzData['eqsl']        ?? null;
    $mqsl      = $qrzData['mqsl']        ?? null;
    $lotw      = $qrzData['lotw']        ?? null;
    $iota      = $qrzData['iota']        ?? null;
    $qslMgr    = $qrzData['qsl_mgr']     ?? null;
    $email     = $qrzData['email']       ?? null;
    $uViews    = $qrzData['u_views']     ?? null;
    $bio       = $qrzData['bio']         ?? null;
    $biodate   = $qrzData['biodate']     ?? null;
    $imageUrl  = $qrzData['image_url']   ?? null;
    $imageinfo = $qrzData['imageinfo']   ?? null;
    $serial    = $qrzData['serial']      ?? null;
    $moddate   = $qrzData['moddate']     ?? null;
    $qrzUser   = $qrzData['qrz_user']    ?? null;
    $qrzUrl    = $qrzData['url']         ?? 'https://www.qrz.com/db/' . $callsign;
    $isRaynet  = (bool) $member;
    $isActive  = $member && $member->status === 'Active';

    $d = '━━━━━━━━━━━━━━━━━━━━━━━━━━━';

    // ── Header ────────────────────────────────────────────────────────────
    $displayName = $nameFmt ?? trim(implode(' ', array_filter([$fname, $name]))) ?: 'Unknown';
    $msg  = "📻 <b>{$call}</b>";
    if ($xref && $xref !== $call) $msg .= " <i>(via {$xref})</i>";
    $msg .= "\n👤 <b>{$displayName}</b>\n";
    if ($nickname && !$nameFmt)  $msg .= "🎙 <i>Known as: {$nickname}</i>\n";
    if ($aliases)                $msg .= "🔁 <i>Aliases: {$aliases}</i>\n";
    if ($pCall)                  $msg .= "📟 <i>Previous: {$pCall}</i>\n";
    $msg .= "\n{$d}\n";

// ── Licence ───────────────────────────────────────────────────────────
    $msg .= "🪪 <b>LICENCE</b>\n";
    if ($licenceLabel) $msg .= "Class: <b>{$licenceLabel}</b>\n";
    if ($codes) {
        $codeMap = ['H' => 'HF Privileges', 'A' => 'Advanced', 'I' => 'Club Station', 'G' => 'GROL'];
        $spelled = implode(', ', array_map(fn($c) => $codeMap[$c] ?? $c, str_split($codes)));
        $msg .= "Codes: {$spelled}\n";
    }
    if ($efdate)  $msg .= "Licensed: {$efdate}\n";
    if ($expdate) $msg .= "Expires: {$expdate}\n";
    if ($born)    $msg .= "Born: {$born}\n";
    $msg .= "\n";

    // ── Address & Location ────────────────────────────────────────────────
    $msg .= "📍 <b>ADDRESS & LOCATION</b>\n";
    if ($attn)   $msg .= "c/o {$attn}\n";
    if ($addr1)  $msg .= "{$addr1}\n";
    $cityLine = implode(', ', array_filter([$addr2, $county, $state, $zip]));
    if ($cityLine) $msg .= "{$cityLine}\n";
    if ($fips)   $msg .= "FIPS: {$fips}\n";
    $countryLine = implode(' / ', array_filter([$country, ($land && $land !== $country) ? $land : null]));
    if ($countryLine) $msg .= "{$countryLine}\n";
    if ($dxcc || $ccode) {
        $dxccStr = implode(' / ', array_filter([$dxcc ? "DXCC #{$dxcc}" : null, $ccode ? "CC #{$ccode}" : null]));
        $msg .= "{$dxccStr}\n";
    }
    if ($msa)      $msg .= "MSA: {$msa}";
    if ($areaCode) $msg .= ($msa ? " · " : "") . "Area Code: {$areaCode}";
    if ($msa || $areaCode) $msg .= "\n";
    $msg .= "\n";

    // ── Grid & Coordinates ────────────────────────────────────────────────
    $msg .= "🗺 <b>GRID & COORDINATES</b>\n";
    if ($grid)         $msg .= "Grid: <b>{$grid}</b>\n";
    if ($lat && $lon)  $msg .= "Coords: <code>{$lat}, {$lon}</code>\n";
    if ($geoloc)       $msg .= "Source: {$geoloc}\n";
    $msg .= "\n";

    // ── Zones & Time ─────────────────────────────────────────────────────
    $msg .= "🌐 <b>ZONES & TIME</b>\n";
    if ($cqZone)  $msg .= "CQ Zone: {$cqZone}\n";
    if ($ituZone) $msg .= "ITU Zone: {$ituZone}\n";
    if ($iota)    $msg .= "IOTA: {$iota}\n";
    if ($timezone) {
        $utcStr = $gmtOff !== null ? " (UTC{$gmtOff})" : '';
        $dstStr = $dst ? " · DST: " . ($dst === 'Y' ? 'Yes' : 'No') : '';
        $msg .= "Timezone: {$timezone}{$utcStr}{$dstStr}\n";
    }
    $msg .= "\n";

    // ── QSL & Contact ─────────────────────────────────────────────────────
    $msg .= "📬 <b>QSL & CONTACT</b>\n";
    $eqslStr = match((string) $eqsl) { '1', 'Y' => '✅', '0', 'N' => '❌', default => '—' };
    $mqslStr = match((string) $mqsl) { '1', 'Y' => '✅', '0', 'N' => '❌', default => '—' };
    $lotwStr = match((string) $lotw) { '1', 'Y' => '✅', '0', 'N' => '❌', default => '—' };
    $msg .= "eQSL: {$eqslStr}  Paper QSL: {$mqslStr}  LoTW: {$lotwStr}\n";
    if ($qslMgr) $msg .= "Manager: {$qslMgr}\n";
    if ($email)  $msg .= "Email: <code>{$email}</code>\n";
    $msg .= "\n";

    // ── QRZ Profile ───────────────────────────────────────────────────────
    $msg .= "🌐 <b>QRZ PROFILE</b>\n";
    if ($uViews)  $msg .= "Page Views: " . number_format((int) $uViews) . "\n";
    if ($bio)     $msg .= "Biography: ✅" . ($biodate ? " (updated {$biodate})" : "") . "\n";
    if ($serial)  $msg .= "Serial: #{$serial}\n";
    if ($moddate) $msg .= "Last modified: {$moddate}\n";
    if ($qrzUser) $msg .= "Managed by: {$qrzUser}\n";
    $msg .= "\n{$d}\n";

    // ── RAYNET Liverpool ──────────────────────────────────────────────────
    $msg .= "📡 <b>RAYNET LIVERPOOL</b>\n";
    if ($isRaynet && $isActive) {
        $msg .= "Status: ✅ Active Member\n";
        if (!empty($member->operator_title)) $msg .= "Role: {$member->operator_title}\n";
        if (!empty($member->dmr_id))         $msg .= "DMR ID: <code>{$member->dmr_id}</code>\n";
        $modes = !empty($member->modes) ? implode(', ', json_decode($member->modes, true) ?? []) : null;
        if ($modes) $msg .= "Modes: {$modes}\n";
        $msg .= "On Call: " . ($member->available_for_callout ? '✅ Yes' : '❌ No') . "\n";
    } elseif ($isRaynet && !$isActive) {
        $msg .= "Status: ⚠️ Inactive Member\n";
    } else {
        $msg .= "Status: ❌ Not a member\n";
    }

    $msg .= "{$d}\n";
    $msg .= "🌐 <a href=\"{$qrzUrl}\">View full profile on QRZ.com</a>";

    if ($imageUrl) {
        $this->sendPhoto($chatId, $threadId, $imageUrl, $msg);
    } else {
        $this->sendMessage($chatId, $threadId, $msg);
    }
}

    protected function setOnCall(int|string $chatId, ?int $threadId, int|string $fromId, bool $onCall): void
    {
        if ((string) $chatId !== (string) $fromId) {
            $this->sendMessage($chatId, $threadId, "⚠️ Please use this command in a <b>direct message</b> to the bot, not in a group.");
            return;
        }
        $user = DB::table('users')->where('telegram_chat_id', (string) $fromId)->first();
        if (!$user) {
            $this->sendMessage($chatId, $threadId, $this->notLinkedMessage($fromId));
            return;
        }
        DB::table('users')->where('id', $user->id)->update(['available_for_callout' => $onCall ? 1 : 0]);
        if ($onCall) {
            $this->sendMessage($chatId, $threadId,
                "✅ <b>You are now ON CALL.</b>\n\n" .
                "The Group Controller can see your status on the portal.\n\n" .
                "Send /offcall or tap Off Call when you are no longer available.",
                $this->mainMenuKeyboard()
            );
        } else {
            $this->sendMessage($chatId, $threadId,
                "🔴 <b>You are now OFF CALL.</b>\n\n" .
                "Send /oncall or tap On Call when you are available again.",
                $this->mainMenuKeyboard()
            );
        }
    }

    protected function startUnavailability(int|string $chatId, ?int $threadId, int|string $fromId): void
    {
        if ((string) $chatId !== (string) $fromId) {
            $this->sendMessage($chatId, $threadId, "⚠️ Please use this command in a <b>direct message</b> to the bot, not in a group.");
            return;
        }
        $user = DB::table('users')->where('telegram_chat_id', (string) $fromId)->first();
        if (!$user) {
            $this->sendMessage($chatId, $threadId, $this->notLinkedMessage($fromId));
            return;
        }
        $this->setState($fromId, [
            'step'      => 'from_date',
            'user_id'   => $user->id,
            'chat_id'   => $chatId,
            'thread_id' => $threadId,
        ]);
        $this->sendMessage($chatId, $threadId,
            "📆 <b>Log Unavailability — Step 1 of 3</b>\n\n" .
            "What is the <b>first day</b> you are unavailable?\n\n" .
            "Type the date in UK format:\n<code>1/5/26</code> or <code>01/05/2026</code>\n\n" .
            "Send /cancel to abort.",
            ['remove_keyboard' => true]
        );
    }

    protected function showMyUnavailability(int|string $chatId, ?int $threadId, int|string $fromId): void
    {
        if ((string) $chatId !== (string) $fromId) {
            $this->sendMessage($chatId, $threadId, "⚠️ Please use this command in a <b>direct message</b> to the bot, not in a group.");
            return;
        }
        $user = DB::table('users')->where('telegram_chat_id', (string) $fromId)->first();
        if (!$user) {
            $this->sendMessage($chatId, $threadId, $this->notLinkedMessage($fromId));
            return;
        }
        $periods = DB::table('member_availabilities')
            ->where('user_id', $user->id)
            ->where('to_date', '>=', now()->format('Y-m-d'))
            ->orderBy('from_date')
            ->get();

        if ($periods->isEmpty()) {
            $this->sendMessage($chatId, $threadId,
                "📆 <b>My Unavailability</b>\n\nYou have no upcoming unavailability periods logged.\n\nUse /unavailability to add one."
            );
            return;
        }

        $this->sendMessage($chatId, $threadId,
            "📆 <b>My Upcoming Unavailability</b>\n\nTap <b>🗑 Remove</b> on any entry to delete it."
        );

        foreach ($periods as $period) {
            $from   = Carbon::parse($period->from_date)->format('d/m/Y');
            $to     = Carbon::parse($period->to_date)->format('d/m/Y');
            $days   = Carbon::parse($period->from_date)->diffInDays(Carbon::parse($period->to_date)) + 1;
            $range  = $from === $to ? $from : "{$from} – {$to}";
            $reason = $period->reason ? "\n📝 {$period->reason}" : "";
            $this->sendMessageWithInline($chatId, $threadId,
                "📅 <b>{$range}</b> ({$days} day" . ($days > 1 ? 's' : '') . "){$reason}",
                [
                    [['text' => '🗑 Remove', 'callback_data' => 'del_unavail:' . $period->id]]
                ]
            );
        }
    }

    // ── HTTP helpers ───────────────────────────────────────────────────────

    protected function sendMessage(int|string $chatId, ?int $threadId, string $text, array $replyMarkup = []): void
    {
        $token   = config('services.telegram.bot_token');
        $payload = ['chat_id' => $chatId, 'text' => $text, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true];
        if ($threadId) $payload['message_thread_id'] = $threadId;
        if (!empty($replyMarkup)) $payload['reply_markup'] = json_encode($replyMarkup);
        Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", $payload);
    }

    protected function sendMessageWithInline(int|string $chatId, ?int $threadId, string $text, array $inlineKeyboard): void
    {
        $token   = config('services.telegram.bot_token');
        $payload = ['chat_id' => $chatId, 'text' => $text, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'reply_markup' => json_encode(['inline_keyboard' => $inlineKeyboard])];
        if ($threadId) $payload['message_thread_id'] = $threadId;
        Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", $payload);
    }

    protected function editMessage(int|string $chatId, int $messageId, string $text): void
    {
        $token = config('services.telegram.bot_token');
        Http::timeout(10)->post("https://api.telegram.org/bot{$token}/editMessageText", [
            'chat_id'      => $chatId,
            'message_id'   => $messageId,
            'text'         => $text,
            'parse_mode'   => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => []]),
        ]);
    }

    protected function answerCallback(string $callbackId, string $text = ''): void
    {
        $token = config('services.telegram.bot_token');
        Http::timeout(10)->post("https://api.telegram.org/bot{$token}/answerCallbackQuery", [
            'callback_query_id' => $callbackId,
            'text'              => $text,
        ]);
    }

    protected function mainMenuKeyboard(): array
    {
        return [
            'keyboard' => [
                [['text' => '📋 Get My Chat ID']],
                [['text' => 'ℹ️ About'], ['text' => '🔕 Help']],
            ],
            'resize_keyboard'   => true,
            'one_time_keyboard' => false,
        ];
    }
    protected function sendPhoto(int|string $chatId, ?int $threadId, string $photoUrl, string $caption): void
{
    $token   = config('services.telegram.bot_token');
    $payload = [
        'chat_id'    => $chatId,
        'photo'      => $photoUrl,
        'caption'    => $caption,
        'parse_mode' => 'HTML',
    ];
    if ($threadId) $payload['message_thread_id'] = $threadId;
    Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendPhoto", $payload);
}

    public static function availableCommands(): array
    {
        return [
            'status'           => ['label' => '🚨 Alert Status',       'group' => 'General'],
            'events'           => ['label' => '📅 Events',              'group' => 'General'],
            'nextevent'        => ['label' => '📅 Next Event',          'group' => 'General'],
            'callsign'         => ['label' => '📻 Callsign Lookup',     'group' => 'General'],
            'myid'             => ['label' => '🆔 My Chat ID',          'group' => 'General'],
            'oncall'           => ['label' => '✅ On Call',              'group' => 'Availability'],
            'offcall'          => ['label' => '🔴 Off Call',            'group' => 'Availability'],
            'unavailability'   => ['label' => '📆 Log Unavailability',  'group' => 'Availability'],
            'myunavailability' => ['label' => '🗓 My Unavailability',   'group' => 'Availability'],
            'topicid'          => ['label' => '📌 Topic ID',            'group' => 'Admin'],
            'getgroupid'       => ['label' => '🆔 Group ID',            'group' => 'Admin'],
        ];
    }

    protected function userCan(int|string $fromId, string $command): bool
{
    $user = DB::table('users')->where('telegram_chat_id', (string) $fromId)->first();
 
    // Unlinked users get read-only public commands only
    if (!$user) {
        return in_array($command, ['status', 'events', 'nextevent', 'callsign', 'myid']);
    }
 
    // Admins are never restricted
    if ($user->is_admin || $user->is_super_admin) return true;
 
    // Admin-only commands are always blocked for regular members
    if (in_array($command, ['topicid', 'getgroupid'])) return false;
 
    // telegram_permissions is now a deny list.
    // null = no entry yet = full access (nothing denied)
    $denied = json_decode($user->telegram_permissions ?? '[]', true) ?? [];
 
    return !in_array($command, $denied);
}

    protected function denyMessage(int|string $chatId, ?int $threadId): void
    {
        $this->sendMessage($chatId, $threadId,
            "⛔ <b>You don't have permission to use this command.</b>\n\n" .
            "Contact your Group Controller if you think this is wrong."
        );
    }

}
