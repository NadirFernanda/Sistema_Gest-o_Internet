<?php

namespace App\Console\Commands;

use App\Models\MikroTikBandwidthSample;
use App\Models\MikroTikSite;
use App\Models\Plano;
use App\Services\MikroTikService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MikroTikSampleBandwidth extends Command
{
    protected $signature = 'mikrotik:sample-bandwidth {--debug : Mostra campos raw das sessões e queues}';
    protected $description = 'Amostra o consumo de largura de banda de todos os clientes PPPoE activos';

    public function handle(): int
    {
        $sites = MikroTikSite::where('active', true)->get();
        if ($sites->isEmpty()) return 0;

        $sampled = 0;

        foreach ($sites as $site) {
            try {
                $service = MikroTikService::forSite($site);
                if (! $service->testConnection()['ok']) continue;

                // Sessões activas: username -> {address, caller-id, uptime}
                $sessions = $service->listActiveSessions();
                $sessionMap = [];
                foreach ($sessions as $s) {
                    $name = $s['name'] ?? $s['=name'] ?? '';
                    if ($name !== '') $sessionMap[$name] = $s;
                }

                if ($this->option('debug')) {
                    $this->line("\n=== SESSIONS (site {$site->host}) ===");
                    foreach ($sessionMap as $u => $s) {
                        $this->line("[$u] " . json_encode(array_filter($s, fn($v) => $v !== '')));
                    }
                }

                // Queues PPPoE: username -> {bytes, max-limit, target}
                $queues = $service->getAllQueueStats();
                $queueMap = [];
                foreach ($queues as $q) {
                    $name = $q['name'] ?? $q['=name'] ?? '';
                    if (preg_match('/pppoe-(\S+?)(?:>|$)/', $name, $m)) {
                        $queueMap[$m[1]] = $q;
                    }
                }

                if ($this->option('debug')) {
                    $this->line("\n=== QUEUES (site {$site->host}) ===");
                    foreach ($queueMap as $u => $q) {
                        $this->line("[$u] bytes=" . ($q['bytes'] ?? $q['=bytes'] ?? 'n/a') . " max-limit=" . ($q['max-limit'] ?? $q['=max-limit'] ?? 'n/a'));
                    }
                }

                $allUsernames = array_unique(array_merge(array_keys($sessionMap), array_keys($queueMap)));
                if (empty($allUsernames)) continue;

                $planos = Plano::whereIn('mikrotik_username', $allUsernames)
                    ->where('estado', 'Ativo')
                    ->get();

                $now = now();

                foreach ($planos as $plano) {
                    $u = $plano->mikrotik_username;
                    $q = $queueMap[$u] ?? null;
                    $s = $sessionMap[$u] ?? null;

                    // Bytes cumulativos: preferir queue (mais fiável), fallback para sessão PPPoE
                    // Na sessão: bytes-out = enviado ao cliente (download), bytes-in = upload
                    if ($q) {
                        [$rxBytes, $txBytes] = $this->parseBytes($q);
                    } elseif ($s) {
                        $rxBytes = (int) ($s['bytes-out'] ?? $s['=bytes-out'] ?? 0);
                        $txBytes = (int) ($s['bytes-in']  ?? $s['=bytes-in']  ?? 0);
                    } else {
                        $rxBytes = $txBytes = 0;
                    }

                    // Taxa calculada contra amostra anterior
                    $prev   = MikroTikBandwidthSample::where('plano_id', $plano->id)
                        ->orderBy('sampled_at', 'desc')->first();
                    $rxRate = 0;
                    $txRate = 0;

                    if ($prev && $prev->sampled_at && ($rxBytes > 0 || $txBytes > 0)) {
                        $interval = max(1, $now->timestamp - $prev->sampled_at->timestamp);
                        if ($rxBytes >= $prev->rx_bytes) {
                            $rxRate = (int) (($rxBytes - $prev->rx_bytes) * 8 / $interval);
                        }
                        if ($txBytes >= $prev->tx_bytes) {
                            $txRate = (int) (($txBytes - $prev->tx_bytes) * 8 / $interval);
                        }
                    }

                    // IP — preferir da sessão activa (mais exacto que o target da queue)
                    $ip = $s['address'] ?? $s['=address'] ?? $q['target'] ?? $q['=target'] ?? null;
                    if ($ip && str_contains($ip, '/')) {
                        $ip = explode('/', $ip)[0];
                    }

                    // MAC address (caller-id no PPPoE)
                    $callerId = $s['caller-id'] ?? $s['=caller-id'] ?? null;

                    // Uptime da sessão
                    $uptimeStr = $s['uptime'] ?? $s['=uptime'] ?? '';
                    $uptimeSeconds = $uptimeStr ? $this->parseUptimeToSeconds($uptimeStr) : 0;

                    // Velocidade máxima do plano:
                    // 1. Preferir da queue (inclui max-limit configurado)
                    // 2. Se sem queue, tentar obter da sessão (rate-limit definido no perfil PPPoE)
                    $maxLimit = $q['max-limit'] ?? $q['=max-limit'] ?? '';
                    if (! $maxLimit && $s) {
                        // A sessão PPPoE pode ter rate-limit definido no perfil
                        $maxLimit = $s['rate-limit'] ?? $s['=rate-limit'] ?? '';
                    }
                    [$maxTxBps, $maxRxBps] = $maxLimit ? $this->parseMaxLimit($maxLimit) : [0, 0];

                    MikroTikBandwidthSample::create([
                        'plano_id'       => $plano->id,
                        'sampled_at'     => $now,
                        'rx_bytes'       => max(0, $rxBytes),
                        'tx_bytes'       => max(0, $txBytes),
                        'rx_rate'        => max(0, $rxRate),
                        'tx_rate'        => max(0, $txRate),
                        'ip_address'     => $ip,
                        'caller_id'      => $callerId,
                        'uptime_seconds' => $uptimeSeconds,
                        'max_rx_bps'     => $maxRxBps,
                        'max_tx_bps'     => $maxTxBps,
                    ]);

                    $sampled++;
                }

                // Manter apenas os últimos 7 dias
                MikroTikBandwidthSample::where('sampled_at', '<', now()->subDays(7))->delete();

            } catch (\Throwable $e) {
                Log::error('MikroTik: falha ao amostrar bandwidth', [
                    'site_id' => $site->id, 'error' => $e->getMessage(),
                ]);
            }
        }

        $this->line("✅ Bandwidth amostrado: {$sampled} clientes");
        return 0;
    }

    private function parseBytes(array $q): array
    {
        $bytes = $q['bytes'] ?? $q['=bytes'] ?? '';
        if ($bytes && str_contains($bytes, '/')) {
            [$in, $out] = explode('/', $bytes, 2);
            return [(int) $in, (int) $out];
        }
        $in  = (int) ($q['bytes-in']  ?? $q['=bytes-in']  ?? 0);
        $out = (int) ($q['bytes-out'] ?? $q['=bytes-out'] ?? 0);
        return [$in, $out];
    }

    private function parseUptimeToSeconds(string $s): int
    {
        $total = 0;
        if (preg_match('/(\d+)w/', $s, $m)) $total += (int)$m[1] * 604800;
        if (preg_match('/(\d+)d/', $s, $m)) $total += (int)$m[1] * 86400;
        if (preg_match('/(\d+)h/', $s, $m)) $total += (int)$m[1] * 3600;
        if (preg_match('/(\d+)m/', $s, $m)) $total += (int)$m[1] * 60;
        if (preg_match('/(\d+)s/', $s, $m)) $total += (int)$m[1];
        return $total;
    }

    private function parseMaxLimit(string $limit): array
    {
        if (! str_contains($limit, '/')) return [0, 0];
        [$txStr, $rxStr] = explode('/', $limit, 2);
        return [$this->parseBps(trim($txStr)), $this->parseBps(trim($rxStr))];
    }

    private function parseBps(string $val): int
    {
        $val = trim(strtolower($val));
        if (str_ends_with($val, 'g')) return (int) $val * 1_000_000_000;
        if (str_ends_with($val, 'm')) return (int) $val * 1_000_000;
        if (str_ends_with($val, 'k')) return (int) $val * 1_000;
        return (int) $val;
    }
}
