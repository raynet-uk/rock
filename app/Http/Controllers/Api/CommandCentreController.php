<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AlertStatus;
use App\Models\CmsLicence;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CommandCentreController extends Controller
{
    private function authenticate(Request $request): bool
    {
        $key = $request->header('X-CMS-Licence');
        if (!$key) return false;
        return CmsLicence::where('key', $key)->where('is_active', true)->exists();
    }

    private function unauthorised()
    {
        return response()->json(['error' => 'Unauthorised'], 401);
    }

    public function resetPassword(Request $request)
    {
        if (!$this->authenticate($request)) return $this->unauthorised();
        $request->validate(['email' => ['required','email'], 'password' => ['required','string','min:10']]);
        $user = User::where('email', $request->email)->first();
        if (!$user) return response()->json(['error' => 'User not found.'], 404);
        $user->password = Hash::make($request->password);
        $user->save();
        return response()->json(['ok' => true, 'name' => $user->name]);
    }

    public function setAlert(Request $request)
    {
        if (!$this->authenticate($request)) return $this->unauthorised();
        $data = $request->validate([
            'level'    => ['required','integer','min:1','max:5'],
            'headline' => ['nullable','string','max:255'],
            'message'  => ['nullable','string'],
        ]);
        $status = AlertStatus::query()->first() ?? new AlertStatus();
        $status->fill($data)->save();
        $meta = $status->meta();
        return response()->json(['ok' => true, 'level' => $status->level, 'title' => $meta['title'], 'colour' => $meta['colour']]);
    }

    public function getSetting(Request $request)
    {
        if (!$this->authenticate($request)) return $this->unauthorised();
        $request->validate(['key' => ['required','string']]);
        return response()->json(['ok' => true, 'key' => $request->key, 'value' => Setting::get($request->key)]);
    }

    public function setSetting(Request $request)
    {
        if (!$this->authenticate($request)) return $this->unauthorised();
        $request->validate(['key' => ['required','string'], 'value' => ['nullable','string']]);
        Setting::set($request->key, $request->value);
        return response()->json(['ok' => true, 'key' => $request->key, 'value' => $request->value]);
    }

    public function getSettings(Request $request)
    {
        if (!$this->authenticate($request)) return $this->unauthorised();
        return response()->json(['ok' => true, 'settings' => Setting::all(['key','value'])->pluck('value','key')]);
    }

    public function getActivityLogs(Request $request)
    {
        if (!$this->authenticate($request)) return $this->unauthorised();
        $logs = ActivityLog::with(['user:id,name,callsign,email', 'loggedByUser:id,name'])
            ->orderBy('event_date', 'desc')
            ->get()
            ->map(fn($l) => [
                'id'         => $l->id,
                'user_id'    => $l->user_id,
                'user_name'  => $l->user?->name,
                'user_email' => $l->user?->email,
                'callsign'   => $l->user?->callsign,
                'event_name' => $l->event_name,
                'event_date' => $l->event_date?->format('Y-m-d'),
                'hours'      => (float) $l->hours,
                'logged_by'  => $l->loggedByUser?->name,
                'created_at' => $l->created_at?->toIso8601String(),
            ]);
        return response()->json(['ok' => true, 'logs' => $logs]);
    }

    public function addActivityLog(Request $request)
    {
        if (!$this->authenticate($request)) return $this->unauthorised();
        $data = $request->validate([
            'user_email' => ['required','email'],
            'event_name' => ['required','string','max:255'],
            'event_date' => ['required','date'],
            'hours'      => ['required','numeric','min:0.25','max:24'],
        ]);
        $user = User::where('email', $data['user_email'])->first();
        if (!$user) return response()->json(['error' => 'User not found.'], 404);
        $log = ActivityLog::create([
            'user_id'    => $user->id,
            'event_name' => $data['event_name'],
            'event_date' => $data['event_date'],
            'hours'      => $data['hours'],
            'logged_by'  => null,
        ]);
        return response()->json(['ok' => true, 'id' => $log->id]);
    }

    public function updateActivityLog(Request $request)
    {
        if (!$this->authenticate($request)) return $this->unauthorised();
        $data = $request->validate([
            'id'         => ['required','integer'],
            'event_name' => ['required','string','max:255'],
            'event_date' => ['required','date'],
            'hours'      => ['required','numeric','min:0.25','max:24'],
        ]);
        $log = ActivityLog::find($data['id']);
        if (!$log) return response()->json(['error' => 'Log not found.'], 404);
        $log->update(['event_name' => $data['event_name'], 'event_date' => $data['event_date'], 'hours' => $data['hours']]);
        return response()->json(['ok' => true]);
    }

    public function deleteActivityLog(Request $request)
    {
        if (!$this->authenticate($request)) return $this->unauthorised();
        $request->validate(['id' => ['required','integer']]);
        $log = ActivityLog::find($request->id);
        if (!$log) return response()->json(['error' => 'Log not found.'], 404);
        $log->delete();
        return response()->json(['ok' => true]);
    }

    public function getMembers(Request $request)
    {
        if (!$this->authenticate($request)) return $this->unauthorised();
        $members = User::orderBy('name')->get()->map(fn($u) => [
            'id'       => $u->id,
            'name'     => $u->name,
            'email'    => $u->email,
            'callsign' => $u->callsign,
        ]);
        return response()->json(['ok' => true, 'members' => $members]);
    }
    public function memberProfile(Request $request)
{
    if (!$this->authenticate($request)) return $this->unauthorised();

    $user = User::where('email', $request->query('email'))->first();
    if (!$user) return response()->json(['error' => 'User not found.'], 404);

    return response()->json([
        'id'                           => $user->id,
        'name'                         => $user->name,
        'email'                        => $user->email,
        'phone'                        => $user->phone,
        'callsign'                     => $user->callsign,
        'dmr_id'                       => $user->dmr_id,
        'echolink_number'              => $user->echolink_number,
        'dstar_callsign'               => $user->dstar_callsign,
        'c4fm_callsign'                => $user->c4fm_callsign,
        'aprs_ssid'                    => $user->aprs_ssid,
        'allstar_node'                 => $user->allstar_node,
        'svxlink_network'              => $user->svxlink_network,
        'raynet_voip'                  => $user->raynet_voip,
        'licence_class'                => $user->licence_class,
        'licence_number'               => $user->licence_number,
        'role'                         => $user->role,
        'level'                        => $user->level,
        'status'                       => $user->status,
        'joined_at'                    => $user->joined_at?->format('d M Y'),
        'notes'                        => $user->notes,
        'available_for_callout'        => $user->available_for_callout,
        'has_vehicle'                  => $user->has_vehicle,
        'vehicle_type'                 => $user->vehicle_type,
        'max_travel_miles'             => $user->max_travel_miles,
        'nok_name'                     => $user->nok_name,
        'nok_relationship'             => $user->nok_relationship,
        'nok_phone'                    => $user->nok_phone,
        'modes'                        => is_array($user->modes) ? $user->modes : json_decode($user->modes ?? '[]', true),
        'completed_course_ids'         => is_array($user->completed_course_ids) ? $user->completed_course_ids : json_decode($user->completed_course_ids ?? '[]', true),
        'attended_event_this_year'     => $user->attended_event_this_year,
        'events_attended_this_year'    => $user->events_attended_this_year,
        'volunteering_hours_this_year' => $user->volunteering_hours_this_year,
        'email_verified_at'            => $user->email_verified_at?->format('d M Y'),
        'is_admin'                     => $user->is_admin,
        'is_super_admin'               => $user->is_super_admin,
        'suspended_at'                 => $user->suspended_at?->format('d M Y H:i'),
        'suspension_message'           => $user->suspension_message,
        'force_password_reset'         => $user->force_password_reset,
        'admin_message'                => $user->admin_message,
        'password_changed_at'          => $user->password_changed_at?->format('d M Y H:i'),
        'created_at'                   => $user->created_at->format('d M Y'),
        'activity_logs'                => ActivityLog::where('user_id', $user->id)
                                            ->orderBy('event_date', 'desc')
                                            ->limit(20)
                                            ->get(['id','event_name','event_date','hours'])
                                            ->map(fn($l) => [
                                                'event_name' => $l->event_name,
                                                'event_date' => $l->event_date->format('d M Y'),
                                                'hours'      => (float) $l->hours,
                                            ]),
    ]);
}
public function updateMemberProfile(Request $request)
{
    if (!$this->authenticate($request)) return $this->unauthorised();

    $user = User::where('email', $request->input('email'))->first();
    if (!$user) return response()->json(['error' => 'User not found.'], 404);

    $data = $request->validate([
        'name'                  => ['sometimes','string','max:255'],
        'phone'                 => ['sometimes','nullable','string','max:30'],
        'callsign'              => ['sometimes','nullable','string','max:20'],
        'dmr_id'                => ['sometimes','nullable','string','max:20'],
        'licence_class'         => ['sometimes','nullable','string'],
        'licence_number'        => ['sometimes','nullable','string','max:30'],
        'role'                  => ['sometimes','nullable','string'],
        'level'                 => ['sometimes','nullable','integer'],
        'status'                => ['sometimes','nullable','string'],
        'joined_at'             => ['sometimes','nullable','date'],
        'notes'                 => ['sometimes','nullable','string'],
        'available_for_callout' => ['sometimes','boolean'],
        'has_vehicle'           => ['sometimes','boolean'],
        'vehicle_type'          => ['sometimes','nullable','string','max:100'],
        'max_travel_miles'      => ['sometimes','nullable','integer'],
        'nok_name'              => ['sometimes','nullable','string','max:255'],
        'nok_relationship'      => ['sometimes','nullable','string','max:100'],
        'nok_phone'             => ['sometimes','nullable','string','max:30'],
        'modes'                 => ['sometimes','nullable','array'],
        'echolink_number'       => ['sometimes','nullable','string','max:20'],
        'dstar_callsign'        => ['sometimes','nullable','string','max:20'],
        'c4fm_callsign'         => ['sometimes','nullable','string','max:20'],
        'aprs_ssid'             => ['sometimes','nullable','string','max:20'],
        'allstar_node'          => ['sometimes','nullable','string','max:20'],
        'svxlink_network'       => ['sometimes','nullable','string','max:100'],
        'raynet_voip'           => ['sometimes','nullable','string','max:20'],
    ]);

    unset($data['email']);
    $user->fill($data)->save();

    return response()->json(['ok' => true]);
}

    public function sendNotification(Request $request)
    {
        if (!$this->authenticate($request)) return $this->unauthorised();

        $data = $request->validate([
            'priority'        => ['required', 'integer', 'min:1', 'max:5'],
            'title'           => ['required', 'string', 'max:255'],
            'body'            => ['nullable', 'string', 'max:2000'],
            'recipient_scope' => ['required', 'in:all,admins,callout,individual'],
            'callsigns'       => ['nullable', 'array'],
        ]);

        $query = User::where('registration_pending', false)
                     ->whereNull('suspended_at');

        if ($data['recipient_scope'] === 'admins') {
            $query->where(function ($q) {
                $q->where('is_admin', true)->orWhere('is_super_admin', true);
            });
        } elseif ($data['recipient_scope'] === 'callout') {
            $query->where('available_for_callout', true);
        } elseif ($data['recipient_scope'] === 'individual') {
            if (empty($data['callsigns'])) {
                return response()->json(['ok' => false, 'error' => 'Individual scope requires callsigns'], 422);
            }
            $needles = array_map('strtoupper', $data['callsigns']);
            $query->where(function ($q) use ($needles, $data) {
                $q->whereIn(\Illuminate\Support\Facades\DB::raw('UPPER(callsign)'), $needles)
                  ->orWhereIn('email', $data['callsigns']);
            });
        }

        $userIds = $query->pluck('id');

        if ($userIds->isEmpty()) {
            return response()->json(['ok' => false, 'error' => 'No matching users found'], 404);
        }

        $notification = \App\Models\AdminNotification::create([
            'title'       => $data['title'],
            'body'        => $data['body'] ?? null,
            'priority'    => $data['priority'],
            'sent_by'     => null,
            'sent_to_all' => $data['recipient_scope'] === 'all',
        ]);

        $rows = $userIds->map(fn ($uid) => [
            'notification_id' => $notification->id,
            'user_id'         => $uid,
            'email_token'     => $data['priority'] <= 3 ? bin2hex(random_bytes(32)) : null,
            'created_at'      => now(),
            'updated_at'      => now(),
        ])->values()->all();

        \App\Models\AdminNotificationRecipient::insert($rows);

        if ($data['priority'] <= 3) {
            $recipients = \App\Models\AdminNotificationRecipient::with('user')
                ->where('notification_id', $notification->id)
                ->get();
            foreach ($recipients as $recipient) {
                if (!$recipient->user) continue;
                try {
                    $recipient->user->notify(
                        new \App\Notifications\AdminNotificationEmail($notification, $recipient->email_token)
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning(
                        'HQ broadcast email failed for user ' . $recipient->user_id . ': ' . $e->getMessage()
                    );
                }
            }
        }

        return response()->json([
            'ok'               => true,
            'members_notified' => $userIds->count(),
            'notification_id'  => $notification->id,
        ]);
    }


    public function notificationStatus(Request $request)
    {
        if (!$this->authenticate($request)) return $this->unauthorised();

        $request->validate(['notification_id' => ['required', 'integer']]);

        $notification = \App\Models\AdminNotification::find($request->notification_id);
        if (!$notification) {
            return response()->json(['ok' => false, 'error' => 'Notification not found'], 404);
        }

        $recipients = $notification->recipients()->whereNull('removed_at')->get();
        $readCount  = $recipients->whereNotNull('read_at')->count();
        $total      = $recipients->count();

        return response()->json([
            'ok'          => true,
            'total_count' => $total,
            'read_count'  => $readCount,
            'unread_count'=> $total - $readCount,
            'read_pct'    => $total > 0 ? round($readCount / $total * 100) : 0,
            'members'     => $recipients->map(fn($r) => [
                'name'     => $r->user->name     ?? '—',
                'callsign' => $r->user->callsign ?? '—',
                'read_at'  => $r->read_at ? $r->read_at->format('d M Y H:i') : null,
            ])->values(),
        ]);
    }

    public function deleteNotification(Request $request)
    {
        if (!$this->authenticate($request)) return $this->unauthorised();

        $data = $request->validate([
            'notification_id' => ['required', 'integer'],
        ]);

        $notification = \App\Models\AdminNotification::find($data['notification_id']);

        if (!$notification) {
            return response()->json(['ok' => false, 'error' => 'Notification not found'], 404);
        }

        // Only delete notifications created by HQ (sent_by is null)
        if ($notification->sent_by !== null) {
            return response()->json(['ok' => false, 'error' => 'Not an HQ notification'], 403);
        }

        $notification->recipients()->delete();
        $notification->delete();

        return response()->json(['ok' => true]);
    }
}
