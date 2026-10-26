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
 *   - frame-ancestors 'none' impede clickjacking, except the SOL ad page with
 *     an explicit HTTPS-origin allowlist.
 *   - form-action 'self' impede submissão de formulários para domínios externos.
 *   - object-src 'none' bloqueia Flash/plugins legacy.
 */
class SecurityHeaders
{
    private const SOL_ADS_ALLOWED_FRAME_ORIGINS = [
        'https://na.solnms.net',
        'https://na.solnms.net:8443',
        'https://na.solnms.net:8445',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Só aplicar em respostas HTML (não em JSON, CSV, PDF, etc.)
        $contentType = $response->headers->get('Content-Type', '');
        if (! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $isSolAdPage = $request->routeIs('ads.portal-wifi');
        $frameAncestors = $isSolAdPage ? $this->solFrameAncestors() : "'none'";
        $csp = $isSolAdPage
            ? implode('; ', [
                "default-src 'self'",
                "script-src 'self'",
                "style-src 'self' 'unsafe-inline'",
                "img-src 'self'",
                "connect-src 'none'",
                "frame-src 'none'",
                "frame-ancestors {$frameAncestors}",
                "form-action 'self'",
                "object-src 'none'",
                "base-uri 'self'",
            ])
            : implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline'",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
                "font-src 'self' data: https://fonts.gstatic.com",
                "img-src 'self' data: https://sg.angolawifi.ao",
                "connect-src 'self'",
                'frame-src https://pagamentonline.emis.co.ao',
                "frame-ancestors 'none'",
                "form-action 'self'",
                "object-src 'none'",
                "base-uri 'self'",
            ]);

        $response->headers->set('Content-Security-Policy', $csp);
        if ($isSolAdPage && $frameAncestors !== "'none'") {
            $response->headers->remove('X-Frame-Options');
        } else {
            $response->headers->set('X-Frame-Options', 'DENY');
        }
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        return $response;
    }

    private function solFrameAncestors(): string
    {
        $configuredOrigins = explode(',', (string) config('services.sol.ads_frame_origins', ''));
        $validOrigins = [];

        foreach ($configuredOrigins as $origin) {
            $origin = trim($origin);
            if (! preg_match('~^https://(\[[0-9a-fA-F:]+\]|[A-Za-z0-9.-]+)(?::([0-9]{1,5}))?$~D', $origin, $matches)) {
                continue;
            }

            $host = trim($matches[1], '[]');
            $isIpv6 = str_starts_with($matches[1], '[');
            if ($isIpv6
                ? filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false
                : filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false
            ) {
                continue;
            }

            if (isset($matches[2]) && ((int) $matches[2] < 1 || (int) $matches[2] > 65535)) {
                continue;
            }

            if (! in_array($origin, self::SOL_ADS_ALLOWED_FRAME_ORIGINS, true)) {
                continue;
            }

            $validOrigins[] = $origin;
        }

        return $validOrigins === []
            ? "'none'"
            : implode(' ', array_unique($validOrigins));
    }
}
