<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // For normal web requests, send them to the member login form.
        if (! $request->expectsJson()) {
            $referer = $request->headers->get('referer', '');
            if (str_contains($referer, 'm0kkn.dragon-net.pl')) {
                session(['login_from' => 'm0kkn']);
            }
            return route('login');
        }

        return null;
    }
}
