<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyApiToken
{
    /**
     * Handle an incoming request.
     * If `API_CLIENTES_TOKEN` is set in .env, require it (either in Authorization Bearer or X-API-TOKEN header).
     */
    public function handle(Request $request, Closure $next)
    {
        $token = env('API_CLIENTES_TOKEN');

        // If no token configured, allow public access
        if (empty($token)) {
            return $next($request);
        }

        $provided = null;
        // Bearer token
        if ($request->bearerToken()) {
            $provided = $request->bearerToken();
        } elseif ($request->header('X-API-TOKEN')) {
            $provided = $request->header('X-API-TOKEN');
        }

        if ($provided && hash_equals($token, $provided)) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized'], 401);
    }
}
