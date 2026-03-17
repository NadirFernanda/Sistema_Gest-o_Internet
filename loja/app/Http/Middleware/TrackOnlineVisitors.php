<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TrackOnlineVisitors
{
    /**
     * Track unique store visitors in the last 5 minutes.
     * Admin and reseller-panel routes are excluded.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('admin*') && ! $request->is('painel-revendedor*')) {
            $key      = 'store_online_visitors';
            $now      = now()->timestamp;
            $ttl      = 300; // 5 minutes

            $visitors = Cache::get($key, []);
            $visitors[session()->getId()] = $now;

            // Prune sessions that have been inactive for more than 5 minutes
            $visitors = array_filter($visitors, fn ($t) => $now - $t < $ttl);

            Cache::put($key, $visitors, $ttl + 60);
        }

        return $next($request);
    }
}
