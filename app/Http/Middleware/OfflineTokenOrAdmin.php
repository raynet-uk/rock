<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Controllers\OfflineTokenController;

class OfflineTokenOrAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Standard session auth — pass through
        if ($request->user() && $request->user()->is_admin) {
            return $next($request);
        }

        // Offline Bearer token
        $bearer = $request->bearerToken();
        if ($bearer) {
            $payload = OfflineTokenController::verify($bearer);
            if ($payload) {
                // Attach a minimal user-like object so controllers work
                $user = \App\Models\User::find($payload['sub']);
                if ($user && $user->is_admin) {
                    auth()->setUser($user);
                    return $next($request);
                }
            }
        }

        return response()->json(['error' => 'Unauthenticated', 'offline_token_expired' => true], 401);
    }
}
