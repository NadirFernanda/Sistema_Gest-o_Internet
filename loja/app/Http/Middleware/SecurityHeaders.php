<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adiciona cabeçalhos de segurança HTTP a todas as respostas web.
 *
 * Content-Security-Policy:
 *   - script-src 'unsafe-inline' aceita os blocos <script> inline das views Blade.
 *   - style-src  'unsafe-inline' aceita os atributos style="" e blocos <style> inline.
 *   - Google Fonts (googleapis + gstatic) permitido para CSS e fontes.
 *   - frame-ancestors 'none' impede clickjacking (equivalente a X-Frame-Options: DENY).
 *   - form-action 'self' impede submissão de formulários para domínios externos.
 *   - object-src 'none' bloqueia Flash/plugins legacy.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Só aplicar em respostas HTML (não em JSON, CSV, PDF, etc.)
        $contentType = $response->headers->get('Content-Type', '');
        if (! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' data: https://fonts.gstatic.com",
            "img-src 'self' data:",
            "connect-src 'self'",
            "frame-src 'none'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "object-src 'none'",
            "base-uri 'self'",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
