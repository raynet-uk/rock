protected function schedule(Schedule $schedule): void
{
        \$schedule->command('cms:check-update')->daily();

    $schedule->command('net:run-scheduler')->everyMinute();
    $schedule->command('rsgb:refresh-news')
        ->everyMinute()
        // ->withoutOverlapping()          // optional but good
        ->runInBackground();               // optional
    
    $schedule->command('rsgb:refresh-region3')->everyMinute();
    $schedule->command('resources:fetch-emails')->everyMinute();
    $schedule->command('raynet:heartbeat')->hourly();
    $schedule->command('raynet:push-report')->hourly();
}
