<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // API routes use X-API-TOKEN header for internal SG frontend calls.
        // Do NOT add StartSession here — it would overwrite the web session cookie
        // and log the user out on every API call made by the frontend JS.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
