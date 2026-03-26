<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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

        $this->configureRateLimiters();
    }

    /**
     * Define os limitadores de taxa da aplicação.
     *
     * 'web-general'  — rotas normais: 120 req/min por IP.
     * 'web-sensitive' — rotas sensíveis (login, OTP, pagamentos): 20 req/min por IP.
     */
    private function configureRateLimiters(): void
    {
        RateLimiter::for('web-general', function (Request $request) {
            $max = (int) config('capacity.rate_limit_per_minute', 120);

            return Limit::perMinute($max)
                ->by($request->ip())
                ->response(function () {
                    return response()->view('errors.429', ['seconds' => 60], 429)
                        ->withHeaders(['Retry-After' => 60]);
                });
        });

        RateLimiter::for('web-sensitive', function (Request $request) {
            $max = (int) config('capacity.rate_limit_sensitive', 20);

            return Limit::perMinute($max)
                ->by($request->ip())
                ->response(function () {
                    return response()->view('errors.429', ['seconds' => 60], 429)
                        ->withHeaders(['Retry-After' => 60]);
                });
        });
    }
}
