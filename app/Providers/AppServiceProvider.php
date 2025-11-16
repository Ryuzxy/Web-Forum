<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Broadcast::routes(['middleware' => ['auth:sanctum', 'web']]);
        // atau gunakan 'auth' untuk cookie-based auth
        // include channels file
        if (file_exists($routes = base_path('routes/channels.php'))) {
            require $routes;
        }
    }
}
