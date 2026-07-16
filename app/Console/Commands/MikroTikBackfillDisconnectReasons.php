<?php

namespace App\Console\Commands;

use App\Models\MikroTikOnlineStatus;
use App\Models\MikroTikOnlineStatusEvent;
use App\Models\MikroTikSite;
use App\Services\MikroTikService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MikroTikBackfillDisconnectReasons extends Command
{
    protected $signature = 'mikrotik:backfill-disconnect-reasons';

    protected $description = 'Tenta preencher razões de desconexão em eventos sem razão definida, usando logs actuais do router';

    public function handle(): int
    {
        $this->info('🔍 Backfill de razões de desconexão PPPoE...');

        $sites = MikroTikSite::where('active', true)->get();

        if ($sites->isEmpty()) {
            $this->warn('Nenhum site activo.');
            return 0;
        }

        // Recolher logs PPP de todos os routers acessíveis
        $allLogs = [];
        foreach ($sites as $site) {
            $service = MikroTikService::forSite($site);
            $test    = $service->testConnection();

            if (! $test['ok']) {
                $this->warn("⚠️  {$site->nome} inacessível: " . ($test['error'] ?? ''));
                continue;
            }

            $logs     = $service->getRecentPppLogs();
            $allLogs  = array_merge($allLogs, $logs);
            $this->line("📡 {$site->nome}: " . count($logs) . " entradas de log PPP recolhidas");
        }

        if (empty($allLogs)) {
            $this->warn('Nenhum log PPP encontrado nos routers. Os logs do MikroTik podem já ter sido sobrescritos.');
            return 0;
        }

        $this->line('📊 Total de entradas de log: ' . count($allLogs));

        // Encontrar eventos offline sem razão ou com razão genérica
        $eventos = MikroTikOnlineStatusEvent::where('event_type', 'offline')
            ->where(fn($q) => $q->whereNull('disconnect_reason')
                ->orWhere('disconnect_reason', 'Queda detectada'))
            ->with('mikrotikOnlineStatus.plano')
            ->orderBy('occurred_at', 'desc')
            ->get();

        $this->line("📋 {$eventos->count()} eventos offline sem razão definida");

        $updated = 0;
        foreach ($eventos as $evento) {
            $username = $evento->mikrotikOnlineStatus?->plano?->mikrotik_username;
            if (! $username) continue;

            $reason = $this->findDisconnectReason($username, $allLogs);

            if ($reason && $reason !== 'Queda detectada') {
                $evento->update(['disconnect_reason' => $reason]);

                // Actualizar também o registo de status se for o evento mais recente
                $status = $evento->mikrotikOnlineStatus;
                if ($status && (! $status->disconnect_reason || $status->disconnect_reason === 'Queda detectada')) {
                    $status->update(['disconnect_reason' => $reason]);
                }

                $updated++;
                $this->line("  ✅ {$username}: {$reason}");
            }
        }

        $this->info("✨ Backfill completo. {$updated}/{$eventos->count()} eventos actualizados.");

        if ($updated === 0) {
            $this->warn('Nenhum evento actualizado — os logs do router provavelmente já não têm entradas antigas suficientes (buffer circular ~1000 entradas).');
        }

        return 0;
    }

    private function findDisconnectReason(string $username, array $logs): string
    {
        // Passo 1: correspondência directa
        foreach ($logs as $entry) {
            $msg = $entry['message'] ?? '';
            if (! str_contains($msg, $username)) continue;
            $r = $this->classify($msg);
            if ($r !== null) return $r;
        }

        // Passo 2: correlação por session ID
        $sessionIds = [];
        foreach ($logs as $entry) {
            $msg = $entry['message'] ?? '';
            if (! str_contains($msg, $username)) continue;
            if (preg_match('/^(<[^>]+>)/', $msg, $m)) {
                $sessionIds[$m[1]] = true;
            }
        }
        foreach ($sessionIds as $sid => $_) {
            foreach ($logs as $entry) {
                $msg = $entry['message'] ?? '';
                if (! str_starts_with($msg, $sid)) continue;
                if (str_contains($msg, $username)) continue;
                $r = $this->classify($msg);
                if ($r !== null) return $r;
            }
        }

        return 'Queda detectada';
    }

    private function classify(string $msg): ?string
    {
        if (str_contains($msg, 'lcp-echo-timeout'))      return 'lcp-echo-timeout (sinal fraco ou cabo)';
        if (str_contains($msg, 'authentication failed')) return 'Falha de autenticação (senha errada)';
        if (str_contains($msg, 'user request'))          return 'Desligado pelo utilizador';
        if (str_contains($msg, 'link failure'))          return 'Falha de ligação física';
        if (str_contains($msg, 'link down'))             return 'Falha de ligação física';
        if (str_contains($msg, 'session timeout'))       return 'Timeout de sessão';
        if (str_contains($msg, 'idle timeout'))          return 'Timeout por inactividade';
        if (str_contains($msg, 'terminated by admin'))   return 'Desconectado pelo administrador';
        if (str_contains($msg, 'admin'))                 return 'Desconectado pelo administrador';
        if (str_contains($msg, 'terminated'))            return 'Sessão terminada';
        if (str_contains($msg, 'auth'))                  return 'Falha de autenticação';
        if (preg_match('/logged\s+out[:\s]+(.+)/i', $msg, $m)) return trim($m[1]);
        if (str_contains($msg, 'disconnected') || str_contains($msg, 'logged out')) return 'Desconectado';
        return null;
    }
}
