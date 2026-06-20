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
    protected $signature = 'mikrotik:sample-bandwidth';
    protected $description = 'Amostra o consumo de largura de banda de todos os clientes PPPoE activos';

    public function handle(): int
    {
        $sites = MikroTikSite::where('active', true)->get();

        if ($sites->isEmpty()) return 0;

        $sampled = 0;

        foreach ($sites as $site) {
            try {
                $service = MikroTikService::forSite($site);
                $test    = $service->testConnection();
                if (! $test['ok']) continue;

                $queues = $service->getAllQueueStats();
                if (empty($queues)) continue;

                // Construir mapa: username -> stats da queue
                // Queue name: "<pppoe-922922966>" ou "pppoe-922922966"
                $queueMap = [];
                foreach ($queues as $q) {
                    $name = $q['name'] ?? '';
                    if (preg_match('/pppoe-(\S+?)(?:>|$)/', $name, $m)) {
                        $queueMap[$m[1]] = $q;
                    }
                }

                if (empty($queueMap)) continue;

                $planos = Plano::whereIn('mikrotik_username', array_keys($queueMap))
                    ->where('estado', 'Ativo')
                    ->get();

                $now = now();

                foreach ($planos as $plano) {
                    $q = $queueMap[$plano->mikrotik_username] ?? null;
                    if (! $q) continue;

                    // Bytes: campo "bytes" = "in/out" ou campos separados "bytes-in"/"bytes-out"
                    [$rxBytes, $txBytes] = $this->parseBytes($q);

                    // Calcular taxa com base na amostra anterior
                    $prev    = MikroTikBandwidthSample::where('plano_id', $plano->id)
                        ->orderBy('sampled_at', 'desc')
                        ->first();

                    $rxRate = 0;
                    $txRate = 0;

                    if ($prev && $prev->sampled_at) {
                        $interval = max(1, $now->timestamp - $prev->sampled_at->timestamp);

                        // Proteger contra reset de contadores (reconnect)
                        if ($rxBytes >= $prev->rx_bytes) {
                            $rxRate = (int) (($rxBytes - $prev->rx_bytes) * 8 / $interval);
                        }
                        if ($txBytes >= $prev->tx_bytes) {
                            $txRate = (int) (($txBytes - $prev->tx_bytes) * 8 / $interval);
                        }
                    }

                    // IP da sessão activa (se disponível na queue)
                    $ip = $q['target'] ?? null;
                    if ($ip && str_contains($ip, '/')) {
                        $ip = explode('/', $ip)[0]; // remover CIDR se existir
                    }

                    MikroTikBandwidthSample::create([
                        'plano_id'   => $plano->id,
                        'sampled_at' => $now,
                        'rx_bytes'   => max(0, $rxBytes),
                        'tx_bytes'   => max(0, $txBytes),
                        'rx_rate'    => max(0, $rxRate),
                        'tx_rate'    => max(0, $txRate),
                        'ip_address' => $ip,
                    ]);

                    $sampled++;
                }

                // Limpar amostras com mais de 7 dias para não crescer indefinidamente
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
        // Tentar campo "bytes" no formato "in/out"
        $bytesStr = $q['bytes'] ?? '';
        if ($bytesStr && str_contains($bytesStr, '/')) {
            [$in, $out] = explode('/', $bytesStr, 2);
            return [(int) $in, (int) $out];
        }

        // Tentar campos separados
        $in  = (int) ($q['bytes-in']  ?? $q['=bytes-in']  ?? 0);
        $out = (int) ($q['bytes-out'] ?? $q['=bytes-out'] ?? 0);
        return [$in, $out];
    }
}
