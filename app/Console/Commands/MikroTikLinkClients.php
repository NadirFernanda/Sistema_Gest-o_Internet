<?php

namespace App\Console\Commands;

use App\Models\MikroTikSite;
use App\Models\Plano;
use App\Services\MikroTikService;
use Illuminate\Console\Command;

class MikroTikLinkClients extends Command
{
    protected $signature = 'mikrotik:link-clients {--fix : Aplica as correcções automaticamente}';
    protected $description = 'Detecta sessões PPPoE sem cliente ligado no sistema e opcionalmente corrige';

    public function handle(): int
    {
        $sites = MikroTikSite::where('active', true)->get();
        if ($sites->isEmpty()) {
            $this->error('Nenhum site MikroTik activo.');
            return 1;
        }

        $fix = $this->option('fix');
        $fixed = 0;
        $unmatched = [];

        foreach ($sites as $site) {
            $service = MikroTikService::forSite($site);
            if (! $service->testConnection()['ok']) {
                $this->warn("Site {$site->host}: sem ligação.");
                continue;
            }

            $sessions = $service->listActiveSessions();
            $this->line("\n[{$site->nome} / {$site->host}] — " . count($sessions) . " sessões activas");

            foreach ($sessions as $s) {
                if (($s['type'] ?? '') !== '!re') continue;

                $username = $s['name'] ?? $s['=name'] ?? '';
                $comment  = $s['comment'] ?? $s['=comment'] ?? '';
                $ip       = $s['address'] ?? $s['=address'] ?? '';

                // Tentar encontrar plano via comentário SGA#{id}|nome
                $plano = null;
                if (preg_match('/SGA#(\d+)\|/', $comment, $m)) {
                    $plano = Plano::find((int) $m[1]);
                }

                // Fallback: procurar plano pelo mikrotik_username
                if (! $plano && $username) {
                    $plano = Plano::where('mikrotik_username', $username)->first();
                }

                if (! $plano) {
                    $unmatched[] = [
                        'site'     => $site->host,
                        'username' => $username,
                        'ip'       => $ip,
                        'comment'  => $comment,
                    ];
                    continue;
                }

                $cliente = $plano->cliente;
                $needsSiteId   = ! $cliente->mikrotik_site_id;
                $needsUsername = ! $plano->mikrotik_username;

                if (! $needsSiteId && ! $needsUsername) continue;

                $status = [];
                if ($needsSiteId)   $status[] = "mikrotik_site_id → {$site->id}";
                if ($needsUsername) $status[] = "mikrotik_username → {$username}";

                $this->line(sprintf(
                    "  [%s] %s (%s): %s",
                    $fix ? '✅ CORRIGIDO' : '⚠️  FALTA',
                    $cliente->nome,
                    $plano->nome,
                    implode(', ', $status)
                ));

                if ($fix) {
                    if ($needsSiteId) {
                        $cliente->mikrotik_site_id = $site->id;
                        $cliente->save();
                    }
                    if ($needsUsername) {
                        $plano->mikrotik_username  = $username;
                        $plano->mikrotik_synced_at = now();
                        $plano->save();
                    }
                    $fixed++;
                }
            }
        }

        if (! empty($unmatched)) {
            $this->line("\n⚠️  Sessões sem plano correspondente no sistema:");
            foreach ($unmatched as $u) {
                $this->line("  [{$u['site']}] username={$u['username']} ip={$u['ip']} comment={$u['comment']}");
            }
        }

        if ($fix) {
            $this->info("\n✅ {$fixed} cliente(s) corrigido(s).");
        } else {
            $this->line("\nCorre com --fix para aplicar as correcções:");
            $this->line("  php artisan mikrotik:link-clients --fix");
        }

        return 0;
    }
}
