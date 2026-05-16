<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackOnlineVisitors
{
    /**
     * Track unique store visitors:
     *  - In-memory 5-min window  → "online agora" no dashboard
     *  - Persistent hourly log   → gráficos de actividade histórica
     *
     * Admin e painel-revendedor são excluídos de ambos.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('admin*') && ! $request->is('painel-revendedor*')) {
            $sid = session()->getId();
            $now = now();
            $ts  = $now->timestamp;
            $ttl = 300;

            // ── 1. In-memory 5-min window (para o KPI "online agora") ────
            $online          = Cache::get('store_online_visitors', []);
            $online[$sid]    = $ts;
            $online          = array_filter($online, fn ($t) => $ts - $t < $ttl);
            Cache::put('store_online_visitors', $online, $ttl + 60);

            // ── 2. Persistent hourly log ──────────────────────────────────
            // Uma escrita por sessão por hora, controlada por flag em Cache.
            $hourKey  = 'vlog_' . $sid . '_' . $now->format('YmdH');
            $isNewHit = ! Cache::has($hourKey);
            if ($isNewHit) {
                Cache::put($hourKey, 1, 3660);
            }

            try {
                $date = $now->toDateString();
                $hour = (int) $now->format('H');
                DB::table('visitor_logs')->upsert(
                    [['date' => $date, 'hour' => $hour, 'sessions' => (int) $isNewHit, 'hits' => 1]],
                    ['date', 'hour'],
                    [
                        'sessions' => DB::raw('visitor_logs.sessions + ' . (int) $isNewHit),
                        'hits'     => DB::raw('visitor_logs.hits + 1'),
                    ]
                );
            } catch (\Throwable) {
                // Falha silenciosa — não interrompe o request por causa de analytics
            }
        }

        return $next($request);
    }
}
