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
use Illuminate\Support\Facades\Notification;
use App\Notifications\Channels\WhatsAppChannel;
use App\Services\WhatsAppService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Ensure the framework Schedule is resolved from the Console Kernel when requested
        $this->app->singleton(\Illuminate\Console\Scheduling\Schedule::class, function ($app) {
            return $app->make(\App\Console\Kernel::class)->resolveConsoleSchedule();
        });
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

        // Register a simple 'whatsapp' notification channel so Notification::send() works
        Notification::extend('whatsapp', function ($app) {
            return new WhatsAppChannel($app->make(WhatsAppService::class));
        });
    }
}
