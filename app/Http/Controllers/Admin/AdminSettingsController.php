<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminSettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings', [
            'supportEmail'            => Setting::get('support_request_email', ''),
            'registrationNotifyEmail' => Setting::get('registration_notify_email', ''),
            'headerCode'              => Setting::get('header_code', ''),
            'telegramGroupChatIds'   => Setting::get('telegram_group_chat_ids', config('services.telegram.group_chat_id', '')),
            'telegramGroupThreadId'  => Setting::get('telegram_group_thread_id', ''),
            'telegramKnownTopics'    => json_decode(Setting::get('telegram_known_topics', '[]'), true) ?? [],
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'support_request_email'     => ['nullable', 'email', 'max:255'],
            'registration_notify_email' => ['nullable', 'email', 'max:255'],
            'header_code'               => ['nullable', 'string'],
            'site_name'                 => ['nullable', 'string', 'max:80'],
            'site_tagline'              => ['nullable', 'string', 'max:120'],
            'site_logo'                 => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'group_name'                => ['nullable', 'string', 'max:80'],
            'group_number'              => ['nullable', 'string', 'max:20'],
            'group_callsign'            => ['nullable', 'string', 'max:20'],
            'group_region'              => ['nullable', 'string', 'max:80'],
            'gc_name'                   => ['nullable', 'string', 'max:80'],
            'gc_email'                  => ['nullable', 'email', 'max:120'],
            'site_url'                  => ['nullable', 'url', 'max:120'],
            'raynet_zone'               => ['nullable', 'string', 'max:20'],
            'telegram_group_chat_ids'    => ['nullable', 'string', 'max:50'],
        ]);

        $groupFields = [
            'group_name', 'group_number', 'group_callsign', 'group_region',
            'gc_name', 'gc_email', 'site_url', 'raynet_zone',
            'site_name', 'site_tagline',
        ];
        foreach ($groupFields as $field) {
            if ($request->has($field)) {
                Setting::set($field, trim($request->input($field, '')));
            }
        }

        if ($request->has('support_request_email')) {
            Setting::set('support_request_email', $request->support_request_email ?? '');
        }
        if ($request->has('registration_notify_email')) {
            Setting::set('registration_notify_email', $request->registration_notify_email ?? '');
        }
        if ($request->has('header_code')) {
            Setting::set('header_code', $request->header_code ?? '');
        }
        if ($request->has('telegram_group_chat_ids')) {
            Setting::set('telegram_group_chat_ids', trim($request->telegram_group_chat_ids ?? ''));
        }
        if ($request->has('telegram_group_thread_id')) {
            Setting::set('telegram_group_thread_id', trim($request->telegram_group_thread_id ?? ''));
        }

        if ($request->boolean('remove_logo')) {
            $old = Setting::get('site_logo_path', '');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
            Setting::set('site_logo_path', '');
        }

        if ($request->hasFile('site_logo')) {
            $old = Setting::get('site_logo_path', '');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
            $ext  = $request->file('site_logo')->getClientOriginalExtension();
            $path = $request->file('site_logo')->storeAs('site', 'logo.' . $ext, 'public');
            Setting::set('site_logo_path', $path);
        }

        return redirect()->route('admin.settings')->with('status', 'Settings saved.');
    }

    public function telegramTest(Request $request, TelegramService $telegram)
    {
        $result = $telegram->sendAlertNotification(3, 'Test Notification', 'This is a test message from your RAYNET admin panel.');
        return back()->with($result ? 'status' : 'error', $result ? 'Test message sent to Telegram successfully.' : 'Failed to send test message. Check your Chat ID and bot token.');
    }

public function updateTelegramPermissions(Request $request): \Illuminate\Http\JsonResponse
{
    $request->validate([
        'user_id' => ['required', 'integer', 'exists:users,id'],
        'denied'  => ['present', 'array'],
        'denied.*'=> ['string'],
    ]);
 
    $availableCommands = array_keys(
        array_filter(
            \App\Http\Controllers\TelegramWebhookController::availableCommands(),
            fn($c) => $c['group'] !== 'Admin'
        )
    );
 
    // Sanitise: only store valid, non-admin command keys
    $denied = array_values(
        array_intersect($request->input('denied', []), $availableCommands)
    );
 
    \Illuminate\Support\Facades\DB::table('users')
        ->where('id', $request->input('user_id'))
        ->update([
            // Store null when nothing is denied (= full access, cleaner than "[]")
            'telegram_permissions' => empty($denied) ? null : json_encode($denied),
            'updated_at'           => now(),
        ]);
 
    return response()->json(['success' => true]);
}
}
