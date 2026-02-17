<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Cliente;
use App\Models\Plano;
use App\Models\Equipamento;
use App\Models\EstoqueEquipamento;
use App\Models\ClienteEquipamento;
use App\Models\Cobranca;
use App\Models\User;

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
        // Register AuditServiceProvider to attach audit observers for core models
        if (class_exists(\App\Providers\AuditServiceProvider::class)) {
            $this->app->register(\App\Providers\AuditServiceProvider::class);
        }
    }
}
