<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use App\Models\AlertStatus;
use App\Services\QRZLookup;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Logout;
use App\Listeners\RecordLoginHistory;
use App\Listeners\RecordLoginFailure;
use App\Listeners\RecordLogout;
use Laravel\Passport\Passport;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once base_path('app/Helpers/helpers.php');
        $this->app->singleton(QRZLookup::class);
    }
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // TESTING ONLY — redirect all photo emails to test address
        if (app()->environment('production') && config('mail.test_override')) {
            \Illuminate\Support\Facades\Mail::alwaysTo(config('mail.test_override'));
        }

        // Share temp admin status with all views
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            if (auth()->check()) {
                $view->with('_isTempAdmin', auth()->user()->isTemporaryAdmin());
            } else {
                $view->with('_isTempAdmin', false);
            }
        });

        // Super-admin bypasses all Gate checks automatically
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super-admin')) {
                return true;
            }
        });
        // Share the current alert status with the main layout
        View::composer('layouts.app', function ($view) {
            $current = AlertStatus::query()->orderByDesc('updated_at')->first();
            $view->with('alertStatus', $current);
        });
        // Record login history
        Event::listen(Login::class,  RecordLoginHistory::class);
        Event::listen(Failed::class, RecordLoginFailure::class);
        Event::listen(Logout::class, RecordLogout::class);

        // ── Passport / SSO ────────────────────────────────────────────────
        Passport::tokensCan([
            'openid'   => 'Verify your identity',
            'profile'  => 'View your name, title and avatar',
            'email'    => 'View your email address',
            'callsign' => 'View your callsign, DMR ID and licence class',
            'role'     => 'View your RAYNET role and permissions',
        ]);
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
        Passport::authorizationView('auth.oauth.authorize');
    }
}