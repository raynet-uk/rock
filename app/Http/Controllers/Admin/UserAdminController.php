<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\AttendanceService;
use App\Services\QRZLookup;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Helpers\AuditLogger;

class UserAdminController extends Controller
{
    public function index()
    {
        // Temporary admins can only see temporary guest/admin accounts
        if (auth()->user()->isTemporaryAdmin()) {
            $roleUserIds = User::role(['temporary_guest', 'temporary_admin'])->pluck('id');
            $users = User::with('roles')
                ->where(function($q) use ($roleUserIds) {
                    $q->whereIn('id', $roleUserIds)
                      ->orWhereNotNull('guest_expires_at');
                })
                ->orderBy('name')
                ->paginate(25);
        } else {
            $users = User::with('roles')->orderBy('name')->paginate(25);
        }

        $suspendedIds = $users->getCollection()
            ->filter(fn($u) => $u->status === 'Suspended')
            ->pluck('id');

        $suspensionLogs = \App\Models\AdminAuditLog::where('action', 'user.suspended')
            ->whereIn('entity_id', $suspendedIds)
            ->where('entity_type', 'User')
            ->orderByDesc('created_at')
            ->get()
            ->keyBy('entity_id');

        $memberCount     = \App\Models\User::role(['admin', 'committee', 'member', 'super-admin'])->count();
        $tempGuestCount  = \App\Models\User::role('temporary_guest')->count();
        $tempAdminCount  = \App\Models\User::role('temporary_admin')->count();
        $testUserCount   = \App\Models\User::role('test_user')->count();
        return view('admin.users.index', compact('users', 'suspensionLogs', 'memberCount', 'tempGuestCount', 'tempAdminCount', 'testUserCount'));
    }

    public function edit($id, QRZLookup $qrz)
    {
        $user = User::findOrFail($id);

        $activityLogs = ActivityLog::where('user_id', $id)
            ->orderByDesc('event_date')
            ->orderByDesc('created_at')
            ->get();

        $qrzData = null;
        if ($user->callsign) {
            $qrzData = $qrz->lookup($user->callsign);
        }

        $sessions = DB::table('sessions')
            ->where('user_id', $id)
            ->orderByDesc('last_activity')
            ->get();

        return view('admin.users.edit', compact('user', 'activityLogs', 'qrzData', 'sessions'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $section = $request->input('_section', 'profile');

        // ── TRAINING TAB ─────────────────────────────────────────────────────
        if ($section === 'training') {
            $completed = $request->input('completed_course_ids', []);
            $user->completed_course_ids = array_map('intval', $completed);
            $user->save();

            AuditLogger::log(
                'user.training_updated',
                $user,
                "Training records updated for {$user->name}"
            );

            return redirect()->route('admin.users.edit', $user->id)
                ->with('status', 'Training records updated.')
                ->with('active_tab', 'training');
        }

        // ── ACCESS TAB ────────────────────────────────────────────────────────
        if ($section === 'access') {
            $request->validate([
                'force_password_reset' => ['nullable', 'boolean'],
            ]);

            $oldReset = $user->force_password_reset;
            $user->force_password_reset = $request->boolean('force_password_reset');
            $user->save();

            AuditLogger::log(
                'user.access_updated',
                $user,
                "Access settings updated for {$user->name}",
                ['force_password_reset' => $oldReset],
                ['force_password_reset' => $user->force_password_reset]
            );

            return redirect()->route('admin.users.edit', $user->id)
                ->with('status', 'Access settings updated.')
                ->with('active_tab', 'access');
        }

        // ── RADIO TAB ─────────────────────────────────────────────────────────
        if ($section === 'radio') {
            $request->validate([
                'licence_class'   => ['nullable', 'in:Foundation,Intermediate,Full'],
                'licence_number'  => ['nullable', 'string', 'max:30'],
                'dmr_id'          => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/'],
                'echolink_number' => ['nullable', 'string', 'max:10', 'regex:/^[0-9]+$/'],
                'dstar_callsign'  => ['nullable', 'string', 'max:15'],
                'c4fm_callsign'   => ['nullable', 'string', 'max:15'],
                'aprs_ssid'       => ['nullable', 'string', 'max:10'],
                'allstar_node'    => ['nullable', 'string', 'max:100'],
                'svxlink_network' => ['nullable', 'string', 'max:100'],
                'raynet_voip'     => ['nullable', 'string', 'max:100'],
                'modes'           => ['nullable', 'array'],
                'modes.*'         => ['string', 'max:30'],
            ]);

            $old = [
                'licence_class'   => $user->licence_class,
                'licence_number'  => $user->licence_number,
                'dmr_id'          => $user->dmr_id,
                'echolink_number' => $user->echolink_number,
                'dstar_callsign'  => $user->dstar_callsign,
                'c4fm_callsign'   => $user->c4fm_callsign,
                'aprs_ssid'       => $user->aprs_ssid,
                'allstar_node'    => $user->allstar_node,
                'svxlink_network' => $user->svxlink_network,
                'raynet_voip'     => $user->raynet_voip,
                'modes'           => $user->modes,
            ];

            $user->licence_class   = $request->licence_class ?: null;
            $user->licence_number  = $request->licence_number ? strtoupper($request->licence_number) : null;
            $user->dmr_id          = $request->dmr_id ?: null;
            $user->echolink_number = $request->echolink_number ?: null;
            $user->dstar_callsign  = $request->dstar_callsign ? strtoupper($request->dstar_callsign) : null;
            $user->c4fm_callsign   = $request->c4fm_callsign ? strtoupper($request->c4fm_callsign) : null;
            $user->aprs_ssid       = $request->aprs_ssid ? strtoupper($request->aprs_ssid) : null;
            $user->allstar_node    = $request->allstar_node ?: null;
            $user->svxlink_network = $request->svxlink_network ?: null;
            $user->raynet_voip     = $request->raynet_voip ?: null;
            $user->modes           = $request->modes ?? [];
            $user->save();

            AuditLogger::log(
                'user.radio_updated',
                $user,
                "Radio details updated for {$user->name}",
                $old,
                [
                    'licence_class'   => $user->licence_class,
                    'licence_number'  => $user->licence_number,
                    'dmr_id'          => $user->dmr_id,
                    'echolink_number' => $user->echolink_number,
                    'dstar_callsign'  => $user->dstar_callsign,
                    'c4fm_callsign'   => $user->c4fm_callsign,
                    'aprs_ssid'       => $user->aprs_ssid,
                    'allstar_node'    => $user->allstar_node,
                    'svxlink_network' => $user->svxlink_network,
                    'raynet_voip'     => $user->raynet_voip,
                    'modes'           => $user->modes,
                ]
            );

            return redirect()->route('admin.users.edit', $user->id)
                ->with('status', 'Radio details updated.')
                ->with('active_tab', 'radio');
        }

        // ── PROFILE TAB ───────────────────────────────────────────────────────
        $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['nullable', 'email', 'max:255'],
            'callsign'              => ['nullable', 'string', 'max:20'],
            'password'              => ['nullable', 'string', 'min:8', 'confirmed'],
            'role'                  => ['nullable', 'string', 'max:100'],
            'level'                 => ['nullable', 'integer', 'min:0', 'max:99'],
            'status'                => ['nullable', 'string', 'max:50'],
            'phone'                 => ['nullable', 'string', 'max:30'],
            'joined_at'             => ['nullable', 'date'],
            'notes'                 => ['nullable', 'string', 'max:2000'],
            'available_for_callout' => ['nullable', 'boolean'],
            'has_vehicle'           => ['nullable', 'boolean'],
            'vehicle_type'          => ['nullable', 'string', 'max:50'],
            'max_travel_miles'      => ['nullable', 'integer', 'min:0', 'max:999'],
            'nok_name'              => ['nullable', 'string', 'max:100'],
            'nok_relationship'      => ['nullable', 'string', 'max:50'],
            'nok_phone'             => ['nullable', 'string', 'max:20'],
            'telegram_chat_id'      => ['nullable', 'string', 'max:50'],
            'created_at_override'   => ['nullable', 'date'],
        ]);

        $user->name           = $request->name;
        $user->email          = $request->email;
        $user->callsign       = $request->callsign ? strtoupper($request->callsign) : null;
        $user->operator_title = $request->role ?: null;
        $user->level          = $request->level !== '' ? $request->level : null;
        $user->status         = $request->status ?: null;
        $user->phone          = $request->phone ?: null;
        $user->joined_at      = $request->joined_at ?: null;
        $user->notes          = $request->notes ?: null;

        // Deployment
        $user->available_for_callout = $request->boolean('available_for_callout');
        $user->has_vehicle           = $request->boolean('has_vehicle');
        $user->vehicle_type          = $request->vehicle_type ?: null;
        $user->max_travel_miles      = $request->max_travel_miles ?: null;

        // Next of kin
        $user->nok_name         = $request->nok_name ?: null;
        $user->nok_relationship = $request->nok_relationship ?: null;
        $user->nok_phone        = $request->nok_phone ?: null;
        $user->telegram_chat_id = $request->telegram_chat_id ?: null;

        // Capture diff before save
        $dirty    = $user->getDirty();
        $original = [];
        $changed  = [];
        foreach ($dirty as $field => $newVal) {
            $original[$field] = $user->getOriginal($field);
            $changed[$field]  = $newVal;
        }

        // Suspension state before save
        $wasSuspended = $user->getOriginal('status') === 'Suspended';
        $nowSuspended = $request->status === 'Suspended';

        // Password change
        $passwordChanged = false;
        if ($request->filled('password')) {
            $user->password            = bcrypt($request->password);
            $user->password_changed_at = now();
            $passwordChanged           = true;
        }

        $user->save();

        // Super admin can override the created_at (member since) date
        if (auth()->user()->isSuperAdmin() && $request->filled('created_at_override')) {
            \Illuminate\Support\Facades\DB::table('users')
                ->where('id', $user->id)
                ->update(['created_at' => \Carbon\Carbon::parse($request->created_at_override)->startOfDay()]);
            AuditLogger::log(
                'user.created_at_overridden',
                $user,
                "Member since date changed for {$user->name} to {$request->created_at_override}"
            );
        }

        // ── Audit logging ─────────────────────────────────────────────────
        if ($passwordChanged) {
            AuditLogger::log(
                'user.password_changed',
                $user,
                "Password changed for {$user->name} by admin"
            );
        }

        if ($nowSuspended && !$wasSuspended) {
            AuditLogger::log(
                'user.suspended',
                $user,
                "Suspended {$user->name}",
                ['status' => $original['status'] ?? null],
                ['status' => 'Suspended']
            );
        } elseif (!$nowSuspended && $wasSuspended) {
            AuditLogger::log(
                'user.unsuspended',
                $user,
                "Unsuspended {$user->name}",
                ['status' => 'Suspended'],
                ['status' => $request->status]
            );
        } elseif (!empty($dirty)) {
            AuditLogger::log(
                'user.profile_updated',
                $user,
                "Profile updated for {$user->name}",
                $original,
                $changed
            );
        }

        return redirect()->route('admin.users.edit', $user->id)
            ->with('status', 'Member profile updated.')
            ->with('active_tab', 'profile');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        AuditLogger::log(
            'user.deleted',
            null,
            "Deleted user {$user->name} (ID #{$user->id}, {$user->email})"
        );

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('status', 'User deleted successfully.');
    }

    /**
     * Manually register a new member from the admin panel.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'                         => ['required', 'string', 'max:255'],
            'email'                        => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'                     => ['required', 'string', 'min:8', 'confirmed'],
            'callsign'                     => ['nullable', 'string', 'max:20', 'unique:users,callsign'],
            'pending_callsign'             => ['nullable', 'string', 'max:20'],
            'role'                         => ['nullable', 'string', 'max:100'],
            'spatie_role'                  => ['nullable', 'string', 'in:member,committee,admin,super-admin'],
            'level'                        => ['nullable', 'integer', 'min:1', 'max:5'],
            'status'                       => ['required', 'in:Active,Standby,Inactive,Suspended'],
            'phone'                        => ['nullable', 'string', 'max:30'],
            'joined_at'                    => ['nullable', 'date'],
            'notes'                        => ['nullable', 'string', 'max:2000'],
            'events_attended_this_year'    => ['nullable', 'integer', 'min:0'],
            'volunteering_hours_this_year' => ['nullable', 'numeric', 'min:0'],
            'attended_event_this_year'     => ['nullable', 'boolean'],
        ]);

        $spatieRole = $request->input('spatie_role', 'member');

        $allowed = ['member', 'committee', 'admin'];
        if (auth()->user()->isSuperAdmin()) {
            $allowed[] = 'super-admin';
        }
        if (!in_array($spatieRole, $allowed)) {
            $spatieRole = 'member';
        }

        $user = User::create([
            'name'                         => $request->name,
            'email'                        => $request->email,
            'password'                     => Hash::make($request->password),
            'password_changed_at'          => now(),
            'email_verified_at'            => $request->boolean('email_verified') ? now() : null,
            'force_password_reset'         => $request->boolean('force_password_reset'),
            'is_admin'                     => in_array($spatieRole, ['admin', 'super-admin']),
            'is_super_admin'               => $spatieRole === 'super-admin',
            'registration_pending'         => $request->boolean('registration_pending'),
            'status'                       => $request->status,
            'callsign'                     => $request->callsign ? strtoupper($request->callsign) : null,
            'pending_callsign'             => $request->pending_callsign ? strtoupper($request->pending_callsign) : null,
            'operator_title'               => $request->role ?: null,
            'level'                        => $request->level ?: null,
            'phone'                        => $request->phone ?: null,
            'joined_at'                    => $request->joined_at ?: null,
            'notes'                        => $request->notes ?: null,
            'events_attended_this_year'    => $request->events_attended_this_year ?? 0,
            'volunteering_hours_this_year' => $request->volunteering_hours_this_year ?? 0,
            'attended_event_this_year'     => $request->boolean('attended_event_this_year'),
        ]);

        $user->syncRoles([$spatieRole]);

        AuditLogger::log(
            'user.created',
            $user,
            "New member created: {$user->name} ({$spatieRole})",
            [],
            ['role' => $spatieRole, 'status' => $user->status]
        );

        if (! $user->email_verified_at) {
            $user->sendEmailVerificationNotification();
        }

        return redirect()->route('admin.users.index')
            ->with('status', "Member '{$request->name}' registered successfully as {$spatieRole}.");
    }

    /**
     * Approve a new member registration.
     */
    public function approveRegistration($id)
    {
        $user = User::findOrFail($id);

        if (! $user->registration_pending) {
            return redirect()->back()
                ->with('status', "{$user->name} does not have a pending registration.");
        }

        $user->registration_pending = false;
        $user->status               = 'Active';
        $user->save();

        if ($user->roles->isEmpty()) {
            $user->syncRoles(['member']);
        }

        AuditLogger::log(
            'user.registration_approved',
            $user,
            "Registration approved for {$user->name}",
            ['registration_pending' => true, 'status' => null],
            ['registration_pending' => false, 'status' => 'Active']
        );

        return redirect()->back()->with(
            'status',
            "Registration approved — {$user->name} is now active."
        );
    }

    /**
     * Reject and permanently delete a new member registration.
     */
    public function rejectRegistration($id)
    {
        $user = User::findOrFail($id);

        if (! $user->registration_pending) {
            return redirect()->back()
                ->with('status', "{$user->name} does not have a pending registration.");
        }

        $name  = $user->name;
        $email = $user->email;

        AuditLogger::log(
            'user.registration_rejected',
            null,
            "Registration rejected and deleted for {$name} ({$email})"
        );

        $user->delete();

        return redirect()->back()
            ->with('status', "Registration for {$name} rejected and removed.");
    }

    public function promote($id)
    {
        $user = User::findOrFail($id);

        if (empty($user->operator_title)) {
            $user->operator_title = 'Operator';
            $user->status         = $user->status ?? 'active';
            $user->save();

            AuditLogger::log(
                'user.promoted',
                $user,
                "{$user->name} promoted to Operator",
                ['operator_title' => null],
                ['operator_title' => 'Operator']
            );

            return redirect()->back()->with('status', "{$user->name} has been set as an Operator.");
        }

        return redirect()->back()->with('status', "{$user->name} already has a role ({$user->operator_title}).");
    }

    public function activityAdd(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'event_hours' => ['required', 'numeric', 'min:0', 'max:24'],
            'event_name'  => ['nullable', 'string', 'max:100'],
            'event_date'  => ['nullable', 'date'],
        ]);

        AttendanceService::recordAttendance(
            $user,
            (float) $request->event_hours,
            $request->event_name ?: null,
            $request->event_date ?: null,
            auth()->id()
        );

        $eventLabel = $request->filled('event_name')
            ? '"' . $request->event_name . '"'
            : 'event';

        AuditLogger::log(
            'user.activity_added',
            $user,
            "Logged {$request->event_hours}h for {$user->name} at {$eventLabel}",
            [],
            ['hours' => $request->event_hours, 'event' => $request->event_name, 'date' => $request->event_date]
        );

        return redirect()
            ->route('admin.users.edit', $user->id)
            ->with('status', "Logged {$request->event_hours}h for {$user->name} ({$eventLabel}).")
            ->with('active_tab', 'activity');
    }

    public function activityOverride(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'attended_event_this_year'     => ['required', 'boolean'],
            'events_attended_this_year'    => ['required', 'integer', 'min:0'],
            'volunteering_hours_this_year' => ['required', 'numeric', 'min:0'],
        ]);

        $old = [
            'attended_event_this_year'     => $user->attended_event_this_year,
            'events_attended_this_year'    => $user->events_attended_this_year,
            'volunteering_hours_this_year' => $user->volunteering_hours_this_year,
        ];

        $user->attended_event_this_year     = (bool) $request->attended_event_this_year;
        $user->events_attended_this_year    = (int) $request->events_attended_this_year;
        $user->volunteering_hours_this_year = round((float) $request->volunteering_hours_this_year, 1);
        $user->save();

        AuditLogger::log(
            'user.activity_override',
            $user,
            "Activity stats manually overridden for {$user->name}",
            $old,
            [
                'attended_event_this_year'     => $user->attended_event_this_year,
                'events_attended_this_year'    => $user->events_attended_this_year,
                'volunteering_hours_this_year' => $user->volunteering_hours_this_year,
            ]
        );

        return redirect()
            ->route('admin.users.edit', $user->id)
            ->with('status', "Activity stats for {$user->name} updated.")
            ->with('active_tab', 'activity');
    }

    public function activityLogUpdate(Request $request, $userId, $logId)
    {
        $user = User::findOrFail($userId);
        $log  = ActivityLog::where('user_id', $userId)->findOrFail($logId);

        $request->validate([
            'event_name' => ['nullable', 'string', 'max:100'],
            'event_date' => ['required', 'date'],
            'hours'      => ['required', 'numeric', 'min:0', 'max:24'],
        ]);

        $old = [
            'event_name' => $log->event_name,
            'event_date' => $log->event_date,
            'hours'      => $log->hours,
        ];

        $log->event_name = $request->event_name ?: null;
        $log->event_date = $request->event_date;
        $log->hours      = round((float) $request->hours, 1);
        $log->save();

        AttendanceService::rebuildAnnualTotals($user);

        AuditLogger::log(
            'user.activity_log_updated',
            $user,
            "Activity log entry updated for {$user->name}",
            $old,
            ['event_name' => $log->event_name, 'event_date' => $log->event_date, 'hours' => $log->hours]
        );

        return redirect()
            ->route('admin.users.edit', $userId)
            ->with('status', 'Activity log entry updated.')
            ->with('active_tab', 'activity');
    }

    public function activityLogDestroy($userId, $logId)
    {
        $user = User::findOrFail($userId);
        $log  = ActivityLog::where('user_id', $userId)->findOrFail($logId);

        AuditLogger::log(
            'user.activity_log_deleted',
            $user,
            "Activity log entry deleted for {$user->name}",
            ['event_name' => $log->event_name, 'event_date' => $log->event_date, 'hours' => $log->hours],
            []
        );

        $log->delete();

        AttendanceService::rebuildAnnualTotals($user);

        return redirect()
            ->route('admin.users.edit', $userId)
            ->with('status', 'Activity log entry removed and totals resynced.')
            ->with('active_tab', 'activity');
    }

    public function grantDmrAccess(\App\Models\User $user)
    {
        $user->givePermissionTo('view dmr dashboard');

        \App\Helpers\AuditLogger::log(
            'dmr_access_granted',
            $user,
            "DMR dashboard access granted to {$user->name} by " . auth()->user()->name
        );

        return back()->with('status', $user->name . ' has been granted DMR dashboard access.');
    }

    public function revokeDmrAccess(\App\Models\User $user)
    {
        $user->revokePermissionTo('view dmr dashboard');

        \App\Helpers\AuditLogger::log(
            'dmr_access_revoked',
            $user,
            "DMR dashboard access revoked from {$user->name} by " . auth()->user()->name
        );

        return back()->with('status', $user->name . "'s DMR dashboard access has been revoked.");
    }

    public function grantDmrMasters(\App\Models\User $user)
    {
        $user->givePermissionTo('view dmr masters');

        \App\Helpers\AuditLogger::log(
            'dmr_masters_access_granted',
            $user,
            "DMR masters access granted to {$user->name} by " . auth()->user()->name
        );

        return back()->with('status', $user->name . ' has been granted DMR masters access.');
    }

    public function revokeDmrMasters(\App\Models\User $user)
    {
        $user->revokePermissionTo('view dmr masters');

        \App\Helpers\AuditLogger::log(
            'dmr_masters_access_revoked',
            $user,
            "DMR masters access revoked from {$user->name} by " . auth()->user()->name
        );

        return back()->with('status', $user->name . "'s DMR masters access has been revoked.");
    }

    public function convertToGuest(Request $request, User $user)
    {
        $request->validate([
            'expires_at' => ['nullable', 'date'],
            'notes'      => ['nullable', 'string', 'max:1000'],
        ]);

        // Remove all existing roles
        $user->syncRoles([]);

        // Assign temporary guest role
        $user->assignRole('temporary_guest');

        // Set expiry and reset notification flag
        $user->update([
            'guest_expires_at'         => $request->expires_at ?? null,
            'guest_expiry_notified_at' => null,
            'notes'                    => $request->notes ?? $user->notes,
        ]);

        \Illuminate\Support\Facades\Log::info('User converted to temporary guest', [
            'user_id'  => $user->id,
            'admin_id' => auth()->id(),
        ]);
        AuditLogger::log('guest.converted_from_member', $user, "Converted {$user->name} from member to temporary guest", [
            'role' => 'member',
        ], [
            'role'       => 'temporary_guest',
            'expires_at' => $user->guest_expires_at?->toDateTimeString(),
        ]);

        // Send conversion notification email if requested
        if ($request->boolean('send_notification')) {
            try {
                \Illuminate\Support\Facades\Mail::to($user->email)
                    ->send(new \App\Mail\TemporaryGuestConverted($user));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send guest conversion email', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        return redirect()
            ->route('admin.users.edit', $user->id)
            ->with('success', "{$user->name} has been converted to a temporary guest account.");
    }

    public function convertToMember(Request $request, User $user)
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Remove temporary guest role
        if ($user->hasRole('temporary_guest')) {
            $user->removeRole('temporary_guest');
        }

        // Assign standard member role
        $user->assignRole('member');

        // Clear all guest expiry fields
        $user->update([
            'guest_expires_at'         => null,
            'guest_expiry_notified_at' => null,
            'notes'                    => $request->notes ?? $user->notes,
        ]);

        \Illuminate\Support\Facades\Log::info('Temporary guest converted to member', [
            'user_id'  => $user->id,
            'admin_id' => auth()->id(),
        ]);
        AuditLogger::log('guest.converted_to_member', $user, "Converted {$user->name} from temporary guest to full member", [
            'role'       => 'temporary_guest',
            'expires_at' => 'cleared',
        ], [
            'role' => 'member',
        ]);

        // Send promotion notification email if requested
        if ($request->boolean('send_notification')) {
            try {
                \Illuminate\Support\Facades\Mail::to($user->email)
                    ->send(new \App\Mail\TemporaryGuestPromoted($user));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send member promotion email', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        return redirect()
            ->route('admin.users.edit', $user->id)
            ->with('success', "{$user->name} has been converted to a full member account.");
    }
}