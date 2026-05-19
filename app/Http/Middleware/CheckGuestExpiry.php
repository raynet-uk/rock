<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Helpers\AuditLogger;

class CheckGuestExpiry
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (
            $user &&
            $user->guest_expires_at &&
            $user->guest_expires_at->isPast()
        ) {
            // Only send email if not already sent by scheduler or previous middleware run
            if (is_null($user->guest_expiry_notified_at)) {

                // Mark FIRST to prevent race condition with scheduler
                $user->update(['guest_expiry_notified_at' => now()]);

                if ($user->hasRole('temporary_guest')) {
                    $user->removeRole('temporary_guest');
                }

                try {
                    Mail::to($user->email)->send(new \App\Mail\TemporaryGuestExpired($user));
                AuditLogger::log('guest.expired', $user, "Temporary guest access expired for {$user->name} — session terminated", [
                    'expires_at' => $user->guest_expires_at?->toDateTimeString(),
                ], ['role' => 'removed']);
                } catch (\Throwable $e) {
                    Log::error('Failed to send guest expiry email', [
                        'user_id' => $user->id,
                        'error'   => $e->getMessage(),
                    ]);
                }
            } else {
                // Email already sent — just clean up role if still attached
                if ($user->hasRole('temporary_guest')) {
                    $user->removeRole('temporary_guest');
                }
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('guest.expired');
        }

        return $next($request);
    }
}
