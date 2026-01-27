<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        // ...existing Laravel middleware...
        \Illuminate\Http\Middleware\HandleCors::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            // ...existing Laravel web middleware...
        ],

        'api' => [
            // ...existing Laravel api middleware...
            \Illuminate\Http\Middleware\HandleCors::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        // ...existing Laravel route middleware...
    ];
}
