<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\FamilyPlanRequest;
use App\Observers\FamilyPlanRequestObserver;

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
        FamilyPlanRequest::observe(FamilyPlanRequestObserver::class);
    }
}
