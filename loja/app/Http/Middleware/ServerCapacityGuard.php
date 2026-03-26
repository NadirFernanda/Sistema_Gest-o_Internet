<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * ServerCapacityGuard
 *
 * Dois sinais independentes protegem o servidor de sobrecarga:
 *
 *  1. Load Average (Linux) — se a carga de CPU do último minuto ultrapassar o
 *     limiar configurado, rejeita imediatamente novos pedidos com HTTP 503.
 *
 *  2. Janela deslizante global — conta pedidos em janelas de 10 s.
 *     Se o volume ultrapassar o limite configurado, rejeita com HTTP 503.
 *
 * Em ambos os casos o cliente recebe uma resposta limpa (HTML ou JSON)
 * com o cabeçalho Retry-After, em vez do servidor travar ou retornar 502/504.
 */
class ServerCapacityGuard
{
    /**
     * Dura 15 s para cobrir a janela de 10 s com folga de expiração.
     */
    private const WINDOW_SECONDS = 10;
    private const WINDOW_TTL     = 15;

    public function handle(Request $request, Closure $next): Response
    {
        // O endpoint de health-check nunca deve ser bloqueado.
        if ($request->is('up')) {
            return $next($request);
        }

        // ── Sinal 1: Load average do sistema operativo ──────────────────────
        if ($this->systemIsOverloaded()) {
            return $this->busyResponse($request);
        }

        // ── Sinal 2: Taxa global de pedidos (janela deslizante) ─────────────
        if ($this->globalRateExceeded()) {
            return $this->busyResponse($request);
        }

        return $next($request);
    }

    // ─── Checks ─────────────────────────────────────────────────────────────

    /**
     * Verifica o load average de 1 minuto (Linux/macOS).
     * Em Windows (desenvolvimento local) a função não existe e retorna false.
     */
    private function systemIsOverloaded(): bool
    {
        if (! function_exists('sys_getloadavg')) {
            return false;
        }

        $load      = sys_getloadavg();
        $threshold = (float) config('capacity.max_load_average', 4.0);

        return $load[0] > $threshold;
    }

    /**
     * Janela deslizante de 10 s: conta pedidos globais.
     * Usa Cache::increment() que é atómico com todos os drivers suportados
     * (database, redis, memcached, file).
     */
    private function globalRateExceeded(): bool
    {
        $max = (int) config('capacity.max_requests_per_window', 200);
        $key = 'cap_rw_' . floor(time() / self::WINDOW_SECONDS);

        $count = Cache::increment($key);

        // Na primeira contagem da janela, define o TTL.
        if ($count === 1) {
            Cache::put($key, 1, now()->addSeconds(self::WINDOW_TTL));
        }

        return $count > $max;
    }

    // ─── Resposta "servidor ocupado" ────────────────────────────────────────

    private function busyResponse(Request $request): Response
    {
        $retryAfter = 10;

        if ($request->expectsJson()) {
            return response()->json([
                'error'       => 'Servidor temporariamente sobrecarregado.',
                'message'     => 'O sistema está a receber demasiados pedidos. Por favor tente novamente em alguns segundos.',
                'retry_after' => $retryAfter,
            ], 503)->withHeaders(['Retry-After' => $retryAfter]);
        }

        return response()
            ->view('errors.503', ['retryAfter' => $retryAfter], 503)
            ->withHeaders(['Retry-After' => $retryAfter]);
    }
}
