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
        // Allow authenticated web sessions (SG frontend internal calls)
        if (auth()->guard('web')->check()) {
            return $next($request);
        }

        $token = config('app.api_clientes_token');

        // Fail-closed: if API_CLIENTES_TOKEN is not configured, block external access
        if (empty($token)) {
            return response()->json(['message' => 'API not available'], 503);
        }

        $provided = $request->bearerToken() ?? $request->header('X-API-TOKEN');

        if ($provided && hash_equals($token, $provided)) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized'], 401);
    }
}
