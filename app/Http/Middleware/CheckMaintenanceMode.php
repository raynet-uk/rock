<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $enabled = filter_var(Setting::get('maintenance_mode', false), FILTER_VALIDATE_BOOLEAN);
        } catch (\Throwable $e) {
            return $next($request);
        }

        if (! $enabled) {
            return $next($request);
        }

        // Always allow admins and super admins through
        if (auth()->check() && auth()->user()->is_admin) {
            return $next($request);
        }

        // Always allow admin routes so admins can log in and turn it off
        if ($request->is('admin', 'admin/*')) {
            return $next($request);
        }

        // Allow auth routes (login, logout, password reset)
        if ($request->is('login', 'logout', 'register', 'password/*', 'email/*', 'change-password')) {
            return $next($request);
        }

        // Allow public API and tracking routes
        if ($request->is('api/*', 'track/*')) {
            return $next($request);
        }

        $isSupportMode = filter_var(Setting::get('maintenance_support_mode', false), FILTER_VALIDATE_BOOLEAN);
        $message = Setting::get('maintenance_message', 'The site is currently undergoing maintenance. Please check back soon.');

        if ($isSupportMode) {
            return response()->view('maintenance-support', [
                'message' => $message,
            ], 503);
        }

        return response()->view('maintenance', [
            'message' => $message,
        ], 503);
    }
}
