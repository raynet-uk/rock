<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
return Application::configure(
    basePath: dirname(__DIR__),
)->withRouting(
    web:      __DIR__ . '/../routes/web.php',
    api:      __DIR__ . '/../routes/api.php',
    commands: __DIR__ . '/../routes/console.php',
    health:   '/up',
)->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('web', \App\Http\Middleware\CheckUpdateInterstitial::class);
    $middleware->appendToGroup('web', [
        \App\Http\Middleware\RedirectIfNotInstalled::class,
        \App\Http\Middleware\CheckMaintenanceMode::class,
        \App\Http\Middleware\CheckSuspended::class,
        \App\Http\Middleware\CheckGuestExpiry::class,
        \App\Http\Middleware\CheckTemporaryAdmin::class,
        \App\Http\Middleware\ForcePasswordChange::class,
    ]);
    // Exclude Passport OAuth endpoints from CSRF verification
    $middleware->validateCsrfTokens(except: [
            'admin/remote-help/notify',
            'admin/remote-help/dismiss-by-code',
        'oauth/authorize',
        'oauth/token',
        'oauth/token/refresh',
        'oauth/introspect',
        'oauth/logout',
        'api/cms/*',
        'telegram/webhook',
        'admin/events/station-log',
        'admin/events/net-status',
        'admin/events/station-log/archive-and-clear',
        'admin/events/station-log/clear',
    ]);
    $middleware->alias([
        // Standard auth aliases
        'auth'        => \App\Http\Middleware\Authenticate::class,
        'guest'       => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'verified'    => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        // Your custom admin gate
        'admin'       => \App\Http\Middleware\AdminMiddleware::class,
        // Super admin gate
        'super.admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
        // Committee gate (committee + admin + super_admin)
        'committee'   => \App\Http\Middleware\CommitteeMiddleware::class,
        'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        // Install wizard
        'not.installed' => \App\Http\Middleware\NotInstalled::class,
        'block.page.editor' => \App\Http\Middleware\BlockPageEditor::class,
        'offline.token'     => \App\Http\Middleware\OfflineTokenOrAdmin::class,
        'net.controller'    => \App\Http\Middleware\NetControllerAccess::class,
    ]);
})->withExceptions(function (Exceptions $exceptions) {
    //
})->create();