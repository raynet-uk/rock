<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\LoginHistory;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuperAdminController extends Controller
{
    // ── MAIN PANEL ────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        // ── Sessions ──────────────────────────────────────────────────────
        $sessions = DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
            ->select(
                'sessions.id',
                'sessions.user_id',
                'sessions.ip_address',
                'sessions.user_agent',
                'sessions.last_activity',
                'users.name as user_name',
                'users.email as user_email',
                'users.callsign as user_callsign',
                'users.is_admin as user_is_admin',
                'users.is_super_admin as user_is_super_admin'
            )
            ->orderByDesc('sessions.last_activity')
            ->get()
            ->map(function ($s) {
                $ua = $s->user_agent ?? '';
                $s->last_activity_at = \Carbon\Carbon::createFromTimestamp($s->last_activity);
                $s->minutes_ago      = $s->last_activity_at->diffInMinutes(now());
                $s->human_ago        = $s->last_activity_at->diffForHumans();

                if (preg_match('/iPad|Tablet/i', $ua))               { $s->device_icon = '📱'; $s->device_type = 'Tablet'; }
                elseif (preg_match('/Mobile|Android|iPhone/i', $ua)) { $s->device_icon = '📱'; $s->device_type = 'Mobile'; }
                elseif (preg_match('/Macintosh|Mac OS/i', $ua))      { $s->device_icon = '💻'; $s->device_type = 'Mac'; }
                elseif (preg_match('/Windows/i', $ua))                { $s->device_icon = '🖥️'; $s->device_type = 'Windows PC'; }
                else                                                  { $s->device_icon = '🌐'; $s->device_type = 'Unknown'; }

                if (preg_match('/Edg/i', $ua))        $s->browser = 'Edge';
                elseif (preg_match('/Firefox/i', $ua)) $s->browser = 'Firefox';
                elseif (preg_match('/Chrome/i', $ua))  $s->browser = 'Chrome';
                elseif (preg_match('/Safari/i', $ua))  $s->browser = 'Safari';
                else                                    $s->browser = 'Unknown';

                $s->is_current = $s->id === session()->getId();
                $s->age_class  = $s->minutes_ago < 5 ? 'fresh' : ($s->minutes_ago < 60 ? 'recent' : 'stale');

                return $s;
            });

        // Session search filter
        if ($request->filled('session_search')) {
            $q = strtolower($request->session_search);
            $sessions = $sessions->filter(fn($s) =>
                str_contains(strtolower($s->user_name ?? ''), $q) ||
                str_contains(strtolower($s->user_email ?? ''), $q) ||
                str_contains(strtolower($s->ip_address ?? ''), $q)
            )->values();
        }

        // ── Login History ─────────────────────────────────────────────────
        $loginQuery = LoginHistory::with('user')->orderByDesc('logged_in_at');

        if ($request->filled('lh_user')) {
            $loginQuery->where(function ($q) use ($request) {
                $q->where('email', 'like', '%' . $request->lh_user . '%')
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', '%' . $request->lh_user . '%'));
            });
        }
        if ($request->filled('lh_status')) {
            $loginQuery->where('successful', $request->lh_status === 'success');
        }
        if ($request->filled('lh_from')) {
            $loginQuery->where('logged_in_at', '>=', $request->lh_from);
        }
        if ($request->filled('lh_to')) {
            $loginQuery->where('logged_in_at', '<=', $request->lh_to . ' 23:59:59');
        }

        $loginHistory = $loginQuery->paginate(25, ['*'], 'lh_page')->withQueryString();

        // ── Audit Log ─────────────────────────────────────────────────────
        $auditQuery = AdminAuditLog::with('admin')->orderByDesc('created_at');

        if ($request->filled('al_admin')) {
            $auditQuery->where('admin_id', $request->al_admin);
        }
        if ($request->filled('al_action')) {
            $auditQuery->where('action', 'like', '%' . $request->al_action . '%');
        }
        if ($request->filled('al_from')) {
            $auditQuery->where('created_at', '>=', $request->al_from);
        }
        if ($request->filled('al_to')) {
            $auditQuery->where('created_at', '<=', $request->al_to . ' 23:59:59');
        }

        $auditLogs = $auditQuery->paginate(25, ['*'], 'al_page')->withQueryString();

        // ── Stats ─────────────────────────────────────────────────────────
        $stats = [
            'active_sessions'     => DB::table('sessions')->count(),
            'logins_today'        => LoginHistory::where('successful', true)->whereDate('logged_in_at', today())->count(),
            'failed_logins_today' => LoginHistory::where('successful', false)->whereDate('logged_in_at', today())->count(),
            'audit_today'         => AdminAuditLog::whereDate('created_at', today())->count(),
            'total_users'         => User::count(),
            'admins'              => User::where('is_admin', true)->count(),
            'super_admins'        => User::where('is_super_admin', true)->count(),
        ];

        // ── Maintenance ───────────────────────────────────────────────────
        $maintenanceMode    = filter_var(Setting::get('maintenance_mode', false), FILTER_VALIDATE_BOOLEAN);
        $maintenanceMessage = Setting::get('maintenance_message', '');

        // ── Admins list for filter dropdown ───────────────────────────────
        $admins = User::where('is_admin', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.super.index', compact(
            'sessions',
            'loginHistory',
            'auditLogs',
            'stats',
            'maintenanceMode',
            'maintenanceMessage',
            'admins'
        ));
    }

    // ── MAINTENANCE ───────────────────────────────────────────────────────

    public function toggleMaintenance(Request $request)
    {
        $request->validate([
            'maintenance_mode'    => ['required', 'in:0,1'],
            'maintenance_message' => ['nullable', 'string', 'max:500'],
        ]);

        $enabled = (bool) $request->maintenance_mode;

        Setting::set('maintenance_mode', $enabled ? '1' : '0');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        Setting::set('maintenance_message', $request->maintenance_message ?? '');

        // ── Additional settings for the enhanced maintenance UI ───────────
        Setting::set('maintenance_title',           $request->input('maintenance_title', 'Back Soon'));
        Setting::set('maintenance_headline',        $request->input('maintenance_headline', ''));
        Setting::set('maintenance_contact',         $request->input('maintenance_contact', ''));
        Setting::set('maintenance_return_at',       $request->input('maintenance_return_at', ''));
        Setting::set('maintenance_auto_disable_at', $request->input('maintenance_auto_disable_at', ''));

        // Record start time when enabling; clear it when disabling
        if ($enabled) {
            Setting::set('maintenance_started_at', now()->toISOString());
        } else {
            Setting::set('maintenance_started_at', '');
        }

        AuditLogger::log(
            $enabled ? 'maintenance.enabled' : 'maintenance.disabled',
            null,
            $enabled
                ? 'Maintenance mode enabled. Message: ' . ($request->maintenance_message ?: '(none)')
                : 'Maintenance mode disabled.'
        );

        return redirect()->route('admin.super.index', ['#maintenance'])
            ->with('status', $enabled
                ? '⚠ Maintenance mode is now ON. Only admins can access the site.'
                : '✓ Maintenance mode is now OFF. The site is publicly accessible.'
            );
    }

    // ── MAINTENANCE IP WHITELIST ──────────────────────────────────────────

    public function whitelistAdd(Request $request): RedirectResponse
    {
        $request->validate(['ip' => ['required', 'string', 'max:50']]);

        $ip   = trim($request->ip);
        $list = json_decode(Setting::get('maintenance_ip_whitelist', '[]'), true) ?? [];

        if (!in_array($ip, $list)) {
            $list[] = $ip;
            Setting::set('maintenance_ip_whitelist', json_encode(array_values($list)));
            AuditLogger::log('Maintenance Whitelist Add', null, "Added IP {$ip} to maintenance bypass whitelist");
        }

        return redirect()->route('admin.super.index', ['#maintenance'])
            ->with('status', "IP {$ip} added to whitelist.");
    }

    public function whitelistRemove(Request $request): RedirectResponse
    {
        $request->validate(['ip' => ['required', 'string']]);

        $ip   = trim($request->ip);
        $list = json_decode(Setting::get('maintenance_ip_whitelist', '[]'), true) ?? [];
        $list = array_values(array_filter($list, fn($x) => $x !== $ip));
        Setting::set('maintenance_ip_whitelist', json_encode($list));
        AuditLogger::log('Maintenance Whitelist Remove', null, "Removed IP {$ip} from maintenance bypass whitelist");

        return redirect()->route('admin.super.index', ['#maintenance'])
            ->with('status', "IP {$ip} removed from whitelist.");
    }

    // ── SESSIONS ──────────────────────────────────────────────────────────

    public function terminateSession(Request $request, string $sessionId)
    {
        if ($sessionId === session()->getId()) {
            return redirect()->back()->with('error', 'You cannot terminate your own session here.');
        }

        $sess = DB::table('sessions')->where('id', $sessionId)->first();
        DB::table('sessions')->where('id', $sessionId)->delete();

        $targetUser = $sess?->user_id ? User::find($sess->user_id) : null;

        AuditLogger::log(
            'session.terminated',
            $targetUser,
            'Terminated session ' . substr($sessionId, 0, 12) . '… for ' . ($targetUser?->name ?? 'guest') . ' (IP: ' . ($sess?->ip_address ?? '?') . ')'
        );

        return redirect()->back()->with('status', '✓ Session terminated.');
    }

    public function terminateUserSessions(Request $request, int $userId)
    {
        $user = User::findOrFail($userId);
        $currentSession = session()->getId();

        $count = DB::table('sessions')
            ->where('user_id', $userId)
            ->where('id', '!=', $currentSession)
            ->count();

        DB::table('sessions')
            ->where('user_id', $userId)
            ->where('id', '!=', $currentSession)
            ->delete();

        AuditLogger::log(
            'session.all_terminated',
            $user,
            "Terminated all {$count} session(s) for {$user->name}"
        );

        return redirect()->back()->with('status', "✓ Terminated {$count} session(s) for {$user->name}.");
    }

    public function terminateAllSessions(Request $request)
    {
        $currentSession = session()->getId();

        $count = DB::table('sessions')
            ->where('id', '!=', $currentSession)
            ->count();

        DB::table('sessions')
            ->where('id', '!=', $currentSession)
            ->delete();

        AuditLogger::log(
            'session.all_terminated',
            null,
            "Terminated all {$count} active sessions across all users (global kill)"
        );

        return redirect()->back()->with('status', "✓ Terminated {$count} sessions across all users. Your own session was preserved.");
    }

    // ── SUPER ADMIN MANAGEMENT ────────────────────────────────────────────

    public function grantSuperAdmin(Request $request, int $userId)
    {
        $user = User::findOrFail($userId);

        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot modify your own super admin status.');
        }

        if (! $user->is_admin) {
            return redirect()->back()->with('error', 'User must be an admin before being elevated to Super Admin.');
        }

        if ($user->is_super_admin) {
            return redirect()->back()->with('error', $user->name . ' is already a Super Admin.');
        }

        $user->update(['is_super_admin' => true]);

        AuditLogger::log(
            'super_admin.granted',
            $user,
            'Super Admin access granted to ' . $user->name . ' (' . $user->email . ')'
        );

        return redirect()->back()->with('status', '★ Super Admin access granted to ' . $user->name . '.');
    }

    public function revokeSuperAdmin(Request $request, int $userId)
    {
        $user = User::findOrFail($userId);

        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot revoke your own super admin status.');
        }

        if (! $user->is_super_admin) {
            return redirect()->back()->with('error', $user->name . ' is not currently a Super Admin.');
        }

        $user->update(['is_super_admin' => false]);

        AuditLogger::log(
            'super_admin.revoked',
            $user,
            'Super Admin access revoked from ' . $user->name . ' (' . $user->email . ')'
        );

        return redirect()->back()->with('status', '✓ Super Admin access revoked from ' . $user->name . '. They retain regular admin access.');
    }
    // ── LOGIN HISTORY DELETE ──────────────────────────────────────────────

    public function deleteLoginHistory(int $id): RedirectResponse
    {
        LoginHistory::findOrFail($id)->delete();
        AuditLogger::log('login_history.deleted', null, "Deleted login history record #{$id}");
        return redirect()->back()->with('status', 'Login record deleted.');
    }

    public function deleteLoginHistoryBulk(Request $request): RedirectResponse
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return redirect()->back()->with('error', 'No records selected.');
        }
        $count = LoginHistory::whereIn('id', $ids)->delete();
        AuditLogger::log('login_history.bulk_deleted', null, "Bulk deleted {$count} login history records");
        return redirect()->back()->with('status', "{$count} login record(s) deleted.");
    }

    public function deleteFailedLogins(): RedirectResponse
    {
        $count = LoginHistory::where('successful', false)->delete();
        AuditLogger::log('login_history.failed_cleared', null, "Cleared {$count} failed login records");
        return redirect()->back()->with('status', "{$count} failed login records cleared.");
    }

    public function deleteAllLoginHistory(): RedirectResponse
    {
        $count = LoginHistory::count();
        LoginHistory::truncate();
        AuditLogger::log('login_history.all_cleared', null, "Cleared entire login history ({$count} records)");
        return redirect()->back()->with('status', "All {$count} login history records deleted.");
    }

}