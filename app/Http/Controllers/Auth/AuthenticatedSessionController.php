<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request): View
    {
        $referer = $request->headers->get('referer', '');
        $fromM0kkn = str_contains($referer, 'm0kkn.dragon-net.pl');

        if ($fromM0kkn) {
            session(['login_from' => 'm0kkn']);
        }

        return view('auth.login', [
            'from' => $fromM0kkn ? 'm0kkn' : session('login_from'),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        // Block accounts awaiting new-member registration approval
        if ($user->registration_pending) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Your account is awaiting approval by a Group Controller.',
            ])->onlyInput('email');
        }

        // Block accounts suspended via the admin suspension system
        // (suspended_at field set by AccountControlController::suspend)
if (! is_null($user->suspended_at)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('suspended_login', [
                'reason' => $user->suspension_message ?: null,
            ]);
        }

        // Block accounts with a legacy status-based suspension
if (in_array($user->status, ['inactive', 'Inactive', 'suspended', 'Suspended'])) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('suspended_login', [
                'reason' => null,
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('members', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
