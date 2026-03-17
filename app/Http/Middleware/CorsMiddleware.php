<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     * CORS is handled by Laravel's built-in HandleCors middleware configured in config/cors.php.
     * This middleware only adds headers for the custom loja origin from env.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $allowedOrigin = config('app.loja_url', env('LOJA_URL', 'http://localhost:3000'));
        $origin = $request->header('Origin', '');

        if ($request->getMethod() === 'OPTIONS') {
            $response = response('', 204);
            if ($origin === $allowedOrigin) {
                $response->headers->set('Access-Control-Allow-Origin', $origin);
                $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
                $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-TOKEN');
            }
            return $response;
        }

        $response = $next($request);
        if ($origin === $allowedOrigin) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-TOKEN');
        }
        return $response;
    }
}
