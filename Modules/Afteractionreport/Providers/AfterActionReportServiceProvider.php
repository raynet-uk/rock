<?php

namespace Modules\Afteractionreport\Providers;

use Illuminate\Support\ServiceProvider;

class AfterActionReportServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'afteractionreport');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
