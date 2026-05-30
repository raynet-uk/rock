<?php
namespace Modules\Drive\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class DriveServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'drive');
    }
}
