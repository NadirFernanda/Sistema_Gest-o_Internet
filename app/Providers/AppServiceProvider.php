<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Garante que o CorsMiddleware seja aplicado ao grupo 'api'
        $this->app['router']->pushMiddlewareToGroup('api', \App\Http\Middleware\CorsMiddleware::class);
    }
}
