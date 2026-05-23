protected function schedule(Schedule $schedule): void
{
    $schedule->command('net:run-scheduler')->everyMinute();
    $schedule->command('rsgb:refresh-news')
        ->everyMinute()
        // ->withoutOverlapping()          // optional but good
        ->runInBackground();               // optional
    
    $schedule->command('rsgb:refresh-region3')->everyMinute();
    $schedule->command('resources:fetch-emails')->everyMinute();
}
