<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        \Log::info('CorsMiddleware executado', [
            'method' => $request->getMethod(),
            'path' => $request->path(),
            'headers' => $request->headers->all()
        ]);

        if ($request->getMethod() === 'OPTIONS') {
            $response = response('', 204);
            $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:3000');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
            \Log::info('CorsMiddleware resposta OPTIONS', ['headers' => $response->headers->all()]);
            return $response;
        }

        $response = $next($request);
        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:3000');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        \Log::info('CorsMiddleware resposta', ['headers' => $response->headers->all()]);
        return $response;
    }
}
