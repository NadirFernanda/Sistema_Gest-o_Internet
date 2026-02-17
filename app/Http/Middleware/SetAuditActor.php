<?php

namespace App\Http\Middleware;

use Closure;

class SetAuditActor
{
    public function handle($request, Closure $next)
    {
        if (auth()->check()) {
            app()->instance('audit.actor', [
                'id' => auth()->id(),
                'role' => auth()->user()->role ?? null,
            ]);
        }

        return $next($request);
    }
}
