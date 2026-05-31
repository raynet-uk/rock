<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RemoteHelpToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RemoteHelpController extends Controller
{
    // ── Any site: request help ────────────────────────────────────────────────
    public function index()
    {
        $active = RemoteHelpToken::where('expires_at', '>', now())
            ->where('used', false)->latest()->get();
        return view('admin.remote-help.index', compact('active'));
    }

    public function generate(Request $request)
    {
        $request->validate(['hours' => ['required','integer','min:1','max:24']]);
        $user  = auth()->user();
        $token = RemoteHelpToken::generate($user->name, $user->email, $request->hours);
        // Notify ROCK provider that a code was generated
        try {
            $notifyUrl = config('raynet.remote_help_provider_url',
                'https://raynet-liverpool.net/admin/remote-help/notify');
            if (!config('raynet.remote_help_provider')) {
                \Illuminate\Support\Facades\Http::timeout(5)->post($notifyUrl, [
                    'site_url'   => config('app.url'),
                    'site_name'  => \App\Models\Setting::get('site_name', config('app.name')),
                    'group_name' => \App\Models\Setting::get('group_name', ''),
                    'code'       => $token->code,
                    'expires_at' => $token->expires_at->toISOString(),
                ]);
            }
        } catch (\Throwable $e) {}

        return back()->with('generated_code', $token->code)
                     ->with('generated_expires', $token->expires_at->format('j M Y H:i'));
    }

    public function revoke(RemoteHelpToken $token)
    {
        // Notify Liverpool to dismiss the pending session
        try {
            $notifyUrl = config('raynet.remote_help_provider_url',
                'https://raynet-liverpool.net/admin/remote-help/notify');
            if (!config('raynet.remote_help_provider')) {
                \Illuminate\Support\Facades\Http::timeout(5)->post(
                    str_replace('/notify', '/dismiss-by-code', $notifyUrl),
                    ['code' => $token->code, 'site_url' => config('app.url')]
                );
            }
        } catch (\Throwable $e) {}

        $token->update(['used' => true]);
        return back()->with('status', 'Access code revoked.');
    }

    // ── Liverpool super-admin: use a code to access another site ─────────────
    public function accessPanel()
    {
        abort_unless(auth()->user()->is_super_admin, 403);
        return view('admin.remote-help.access-panel');
    }

    public function accessRedirect(Request $request)
    {
        abort_unless(auth()->user()->is_super_admin, 403);
        $request->validate([
            'site_url' => ['required','url'],
            'code'     => ['required','string'],
        ]);

        $url  = rtrim($request->site_url, '/');
        $code = strtoupper(trim($request->code));

        return redirect($url . '/admin/remote-help/login?code=' . urlencode($code)
            . '&from=' . urlencode(config('app.url')));
    }

    // ── Any site: receive a remote login — show system info first ───────────
    public function remoteLogin(Request $request)
    {
        $code  = strtoupper(trim($request->query('code', '')));
        $from  = $request->query('from', '');

        $token = RemoteHelpToken::where('code', $code)->first();

        if (!$token || !$token->isValid()) {
            abort(403, 'Invalid or expired remote help code.');
        }

        // If confirmed=1, proceed with login
        if ($request->query('confirmed') === '1') {
            $token->update([
                'accessed_at'    => now(),
                'accessed_by_ip' => $request->ip(),
            ]);

            $admin = User::where('is_super_admin', true)->first()
                  ?? User::where('is_admin', true)->first();

            if (!$admin) abort(403, 'No admin user found on this site.');

            Auth::login($admin);

            session([
                'remote_help_expires' => $token->expires_at->timestamp,
                'remote_help_code'    => $code,
                'remote_help_from'    => $from,
            ]);

            return redirect()->route('admin.dashboard')
                ->with('remote_help_active', true);
        }

        // Show system info review page first
        $sysInfo = $this->buildSystemInfo()->getData(true);
        $confirmUrl = url('/admin/remote-help/login') . '?code=' . urlencode($code) . '&from=' . urlencode($from) . '&confirmed=1';

        return view('admin.remote-help.review', compact('sysInfo', 'confirmUrl', 'token', 'from'));
    }

    // ── Expire check middleware helper ────────────────────────────────────────
    public static function checkExpiry(): void
    {
        if (session('remote_help_expires') && now()->timestamp > session('remote_help_expires')) {
            Auth::logout();
            session()->flush();
        }
    }

// ── System info for remote support ───────────────────────────────────────
    public function systemInfo(): \Illuminate\Http\JsonResponse
    {
        abort_unless(auth()->user()?->is_admin, 403);
        return $this->buildSystemInfo();
    }

    private function buildSystemInfo(): \Illuminate\Http\JsonResponse
    {
        $env = config('database.connections.mysql');
        $disk = disk_free_space(base_path());
        $diskTotal = disk_total_space(base_path());

        return response()->json([
            'Site' => [
                'URL'             => config('app.url'),
                'Group Name'      => \App\Models\Setting::get('group_name','—'),
                'Laravel Version' => app()->version(),
                'PHP Version'     => PHP_VERSION,
                'Environment'     => config('app.env'),
                'Debug Mode'      => config('app.debug') ? 'ON ⚠' : 'off',
            ],
            'Server' => [
                'Hostname'        => gethostname(),
                'Server IP'       => $_SERVER['SERVER_ADDR'] ?? '—',
                'Web Root'        => base_path(),
                'Disk Free'       => round($disk/1073741824,2) . ' GB',
                'Disk Total'      => round($diskTotal/1073741824,2) . ' GB',
                'Disk Used %'     => round((($diskTotal-$disk)/$diskTotal)*100,1) . '%',
                'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? '—',
            ],
            'Database' => [
                'Host'            => $env['host'] ?? '—',
                'Port'            => $env['port'] ?? '3306',
                'Database'        => $env['database'] ?? '—',
                'Username'        => $env['username'] ?? '—',
                'Password'        => $env['password'] ?? '—',
                'Connection'      => config('database.default'),
            ],
            'Mail' => [
                'Host'            => config('mail.mailers.smtp.host','—'),
                'Port'            => config('mail.mailers.smtp.port','—'),
                'Username'        => config('mail.mailers.smtp.username','—'),
                'From Address'    => config('mail.from.address','—'),
                'Encryption'      => config('mail.mailers.smtp.encryption','—'),
            ],
            'SSH / Admin' => [
                'cPanel User'     => explode('_', $env['username'] ?? '')[0] ?? '—',
                'Home Directory'  => '/home/' . (explode('_', $env['username'] ?? '')[0] ?? '?'),
                'Public HTML'     => base_path(),
                'Storage Path'    => storage_path(),
                'Log File'        => storage_path('logs/laravel.log'),
            ],
        ]);
    }

    // ── Dismiss notification by code (called when remote site revokes) ─────────
    public function dismissByCode(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        \Illuminate\Support\Facades\DB::table('remote_help_notifications')
            ->where('code', strtoupper($request->input('code', '')))
            ->where('site_url', rtrim($request->input('site_url', ''), '/'))
            ->update(['dismissed' => true]);
        return response()->json(['success' => true]);
    }

    // ── Receive notification from remote site that a code was generated ────────
    public function receiveNotification(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'site_url'   => ['required', 'url', 'max:255'],
            'site_name'  => ['nullable', 'string', 'max:100'],
            'group_name' => ['nullable', 'string', 'max:100'],
            'code'       => ['required', 'string', 'max:20'],
            'expires_at' => ['required', 'date'],
        ]);

        \Illuminate\Support\Facades\DB::table('remote_help_notifications')
            ->where('expires_at', '<', now())
            ->orWhere('dismissed', true)
            ->delete();

        \Illuminate\Support\Facades\DB::table('remote_help_notifications')->insert([
            'site_url'   => rtrim($request->input('site_url'), '/'),
            'site_name'  => $request->input('site_name'),
            'group_name' => $request->input('group_name'),
            'code'       => strtoupper($request->input('code')),
            'expires_at' => \Carbon\Carbon::parse($request->input('expires_at'))->format('Y-m-d H:i:s'),
            'dismissed'  => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    // ── Return pending sessions for the access panel ──────────────────────────
    public function pendingSessions(): \Illuminate\Http\JsonResponse
    {
        abort_unless(auth()->check() && auth()->user()->is_admin, 403);

        $sessions = \Illuminate\Support\Facades\DB::table('remote_help_notifications')
            ->where('dismissed', false)
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($s) => [
                'id'         => $s->id,
                'site_url'   => $s->site_url,
                'site_name'  => $s->site_name,
                'group_name' => $s->group_name,
                'code'       => $s->code,
                'expires_at' => $s->expires_at,
                'created_at' => $s->created_at,
            ]);

        return response()->json(['sessions' => $sessions]);
    }

    // ── Dismiss a pending session notification ────────────────────────────────
    public function dismissSession(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        abort_unless(auth()->check() && auth()->user()->is_admin, 403);
        \Illuminate\Support\Facades\DB::table('remote_help_notifications')
            ->where('id', $request->input('id'))
            ->update(['dismissed' => true]);
        return response()->json(['success' => true]);
    }

}
