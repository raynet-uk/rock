protected function schedule(Schedule $schedule): void
{
    $schedule->command('rsgb:refresh-news')
        ->everyThirtyMinutes()
        // ->withoutOverlapping()          // optional but good
        ->runInBackground();               // optional
    
    $schedule->command('resources:fetch-emails')->everyMinute();
}
