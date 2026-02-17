<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\AuditObserver;
use App\Models\Cliente;
use App\Models\Cobranca;
use App\Models\Equipamento;
use App\Models\Plano;

class AuditServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Register observers for key models. Add other models as needed.
        if (class_exists(Cliente::class)) {
            Cliente::observe(AuditObserver::class);
        }
        if (class_exists(Cobranca::class)) {
            Cobranca::observe(AuditObserver::class);
        }
        if (class_exists(Plano::class)) {
            Plano::observe(AuditObserver::class);
        }
        if (class_exists(Equipamento::class)) {
            Equipamento::observe(AuditObserver::class);
        }
    }
}
