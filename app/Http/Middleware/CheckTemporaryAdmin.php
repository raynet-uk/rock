<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CheckTemporaryAdmin
{
    // Routes a temporary admin can access (read-only views only)
    protected array $allowedRoutes = [
        'admin.dashboard',
        'admin.users.index',
        'admin.temporary-guests.index',
        'admin.temporary-guests.create',
        'admin.temporary-guests.edit',
        'admin.events',
        'admin.events.show',
        'admin.online',
    ];

    // HTTP methods that are write operations
    protected array $writeMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! $user->isTemporaryAdmin()) {
            return $next($request);
        }

        $currentRoute = $request->route()?->getName();

        // Block ALL write operations except creating/managing temporary guests
        if (in_array($request->method(), $this->writeMethods)) {
            $allowedWrites = [
                'admin.temporary-guests.store',
                'admin.temporary-guests.update',
                'admin.temporary-guests.destroy',
                'admin.temporary-guests.disable',
                'admin.temporary-guests.reinstate',
                'logout',
            ];

            if (! in_array($currentRoute, $allowedWrites)) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Read-only access — this action is not permitted.'], 403);
                }
                return redirect()->back()->with('temp_admin_blocked', 'This action is restricted. Temporary administrators have read-only access and can only manage temporary guest accounts.');
            }
        }

        // Block access to editing non-temporary users
        if ($currentRoute === 'admin.users.edit') {
            $userId = $request->route('user');
            // Handle both model binding and raw ID
            if (is_object($userId)) {
                $targetUser = $userId;
            } else {
                $targetUser = \App\Models\User::find($userId);
            }
            if ($targetUser && ! $targetUser->isTemporaryGuest() && ! $targetUser->isTemporaryAdmin()) {
                return redirect()->route('admin.temporary-guests.index')
                    ->with('temp_admin_blocked', 'Temporary administrators can only view temporary guest accounts.');
            }
        }

        // Block sensitive admin sections entirely
        $blockedPrefixes = [
            // Super admin — never accessible to temp admins
            'admin.super',
            // OAuth / CMS — sensitive system config
            'admin.oauth',
            'admin.cms',
            // Write-only user actions
            'admin.impersonate',
            'admin.callsign',
            'admin.users.roles',
            'admin.users.force',
            'admin.users.suspend',
            'admin.users.unsuspend',
            'admin.users.verify',
            'admin.users.send',
            'admin.users.session',
            'admin.users.message',
            'admin.users.dmr',
            'admin.users.activity',
            'admin.users.avatar',
            'admin.users.store',
            'admin.users.update',
            'admin.users.destroy',
            'admin.users.promote',
            'admin.broadcast',
            // Notifications — contains member PII
            'admin.notifications',
        ];

        foreach ($blockedPrefixes as $prefix) {
            if (str_starts_with($currentRoute ?? '', $prefix)) {
                return redirect()->route('admin.temporary-guests.index')
                    ->with('temp_admin_blocked', 'This section is not available to temporary administrators.');
            }
        }

        return $next($request);
    }
}
