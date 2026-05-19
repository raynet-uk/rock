<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console routes
|--------------------------------------------------------------------------
|
| This file is where you define console-only routes and the scheduler.
| The important bit for us is telling Laravel:
|   "Run condx:generate once per day at a specific time."
|
| The actual cron on the server only ever calls:
|   php artisan schedule:run
| Laravel then decides WHEN condx:generate runs.
|
*/

/**
 * Example built-in demo command from the Laravel skeleton.
 * Safe to keep; not required for the propagation brief.
 */
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Daily UK Propagation Brief
|--------------------------------------------------------------------------
|
| This schedules our custom command:
|   php artisan condx:generate
|
| Behaviour:
| - Runs once per day at 07:10, server time adjusted to Europe/London.
| - withoutOverlapping() stops a second run starting if the first one
|   is still going (e.g. if an API hangs).
|
| You can change '07:10' to any HH:MM you prefer.
|
*/

Schedule::command('condx:generate')
    ->dailyAt('07:10')
    ->timezone('Europe/London')
    ->withoutOverlapping();

Schedule::command('raynet:reset-annual-stats')
    ->yearlyOn(9, 1, '00:05')
    ->timezone('Europe/London')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/annual-reset.log'));
Schedule::command('lms:daily')
    ->dailyAt('08:00')
    ->timezone('Europe/London')
    ->withoutOverlapping();
// Push report to Command Centre daily
\Illuminate\Support\Facades\Schedule::command('raynet:push-report')->dailyAt('06:00');
\Illuminate\Support\Facades\Schedule::command('raynet:heartbeat')->everyFifteenMinutes();

// Fetch emailed resources every minute
Schedule::command('resources:fetch-emails')
    ->everyMinute()
    ->withoutOverlapping();

// Check for expired resources daily
Schedule::call(function () {
    app(\App\Http\Controllers\ResourceController::class)->checkExpired();
})->daily()->name('resources:check-expired');

// Auto-expire temporary guest accounts
Schedule::command('guests:expire')->everyMinute()->timezone('Europe/London')->withoutOverlapping();
