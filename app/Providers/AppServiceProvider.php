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
use App\Observers\ModelAuditObserver;

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
        // (AuditServiceProvider removed) — legacy audit observers unregistered
        // Register generic model observer for critical models
        Cliente::observe(new ModelAuditObserver());
        Plano::observe(new ModelAuditObserver());
        Equipamento::observe(new ModelAuditObserver());
        EstoqueEquipamento::observe(new ModelAuditObserver());
        ClienteEquipamento::observe(new ModelAuditObserver());
        Cobranca::observe(new ModelAuditObserver());
        User::observe(new ModelAuditObserver());
    }
}
