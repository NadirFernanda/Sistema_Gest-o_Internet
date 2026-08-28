<?php

namespace App\Http\Middleware;

use Closure;
use GuzzleHttp\Client;
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

            // ── 0. País do visitante (geolocalização por IP, cache 1h) ────────
            $country = $this->resolveCountry($request->ip());

            // ── 1. In-memory 5-min window (para o KPI "online agora") ────
            $online       = Cache::get('store_online_visitors', []);
            $online[$sid] = ['ts' => $ts, 'country' => $country];
            $online       = array_filter($online, fn ($v) => $ts - (is_array($v) ? $v['ts'] : $v) < $ttl);
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

                // Log por país (uma entrada por sessão por dia por país)
                if ($isNewHit && $country !== 'Local') {
                    DB::table('visitor_country_logs')->upsert(
                        [['date' => $date, 'country' => $country, 'sessions' => 1]],
                        ['date', 'country'],
                        ['sessions' => DB::raw('visitor_country_logs.sessions + 1')]
                    );
                }
            } catch (\Throwable) {
                // Falha silenciosa — não interrompe o request por causa de analytics
            }
        }

        return $next($request);
    }

    private function resolveCountry(string $ip): string
    {
        // IPs privados/localhost
        if (in_array($ip, ['127.0.0.1', '::1'])
            || str_starts_with($ip, '192.168.')
            || str_starts_with($ip, '10.')
            || str_starts_with($ip, '172.')) {
            return 'Local';
        }

        $cacheKey = 'geoip_' . md5($ip);
        return Cache::remember($cacheKey, 3600, function () use ($ip) {
            try {
                $res = (new Client(['timeout' => 2]))->get(
                    "http://ip-api.com/json/{$ip}?fields=country,countryCode&lang=pt"
                );
                if ($res->getStatusCode() === 200) {
                    $data = json_decode((string) $res->getBody(), true);
                    return $data['country'] ?? 'Desconhecido';
                }
            } catch (\Throwable) {
                // API inacessível — fallback silencioso
            }
            return 'Angola'; // fallback padrão para o mercado principal
        });
    }
}
