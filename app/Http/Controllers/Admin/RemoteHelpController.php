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
        return back()->with('generated_code', $token->code)
                     ->with('generated_expires', $token->expires_at->format('j M Y H:i'));
    }

    public function revoke(RemoteHelpToken $token)
    {
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

    // ── Any site: receive a remote login from Liverpool ───────────────────────
    public function remoteLogin(Request $request)
    {
        $code  = strtoupper(trim($request->query('code', '')));
        $from  = $request->query('from', '');

        $token = RemoteHelpToken::where('code', $code)->first();

        if (!$token || !$token->isValid()) {
            abort(403, 'Invalid or expired remote help code.');
        }

        // Mark as accessed but NOT used — allow multiple page loads during session
        $token->update([
            'accessed_at'    => now(),
            'accessed_by_ip' => $request->ip(),
        ]);

        // Log in as first super admin
        $admin = User::where('is_super_admin', true)->first()
              ?? User::where('is_admin', true)->first();

        if (!$admin) abort(403, 'No admin user found on this site.');

        Auth::login($admin);

        // Store expiry in session so we can auto-logout
        session([
            'remote_help_expires' => $token->expires_at->timestamp,
            'remote_help_code'    => $code,
            'remote_help_from'    => $from,
        ]);

        return redirect()->route('admin.dashboard')
            ->with('remote_help_active', true);
    }

    // ── Expire check middleware helper ────────────────────────────────────────
    public static function checkExpiry(): void
    {
        if (session('remote_help_expires') && now()->timestamp > session('remote_help_expires')) {
            Auth::logout();
            session()->flush();
        }
    }
}
