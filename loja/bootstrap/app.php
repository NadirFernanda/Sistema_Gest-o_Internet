<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\ThrottleRequests;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'sg-admin'       => \App\Http\Middleware\VerifySgAdminAccess::class,
            'throttle.sensitive' => ThrottleRequests::using('web-sensitive'),
        ]);

        // ── Camada 1: Guarda de capacidade (antes de tudo o resto)
        // Rejeita com 503 se o servidor estiver sobrecarregado.
        $middleware->prepend(\App\Http\Middleware\ServerCapacityGuard::class);

        $middleware->web(append: [
            // ── Camada 2: Rate limit por IP (120 req/min — protege contra abuso)
            ThrottleRequests::using('web-general'),
            // ── Camada 3: Rastreio de visitantes online (existente)
            \App\Http\Middleware\TrackOnlineVisitors::class,
        ]);

        // Gateway de pagamento não pode enviar token CSRF — isentar o webhook
        $middleware->validateCsrfTokens(except: [
            'payment/familia/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Personalizar resposta 429 (ThrottleRequestsException) com vista em PT
        $exceptions->render(function (
            \Illuminate\Http\Exceptions\ThrottleRequestsException $e,
            \Illuminate\Http\Request $request
        ) {
            $retryAfter = (int) ($e->getHeaders()['Retry-After'] ?? 60);

            if ($request->expectsJson()) {
                return response()->json([
                    'error'       => 'Demasiados pedidos.',
                    'message'     => 'Aguarde antes de tentar novamente.',
                    'retry_after' => $retryAfter,
                ], 429)->withHeaders($e->getHeaders());
            }

            return response()
                ->view('errors.429', ['seconds' => $retryAfter], 429)
                ->withHeaders($e->getHeaders());
        });
    })
    ->create();
