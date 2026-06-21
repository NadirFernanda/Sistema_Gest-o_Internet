<?php

namespace App\Console\Commands;

use App\Models\MikroTikSite;
use App\Services\MikroTikService;
use Illuminate\Console\Command;

class MikroTikSetRateLimit extends Command
{
    protected $signature = 'mikrotik:set-rate-limit {username : Username PPPoE} {rate : Rate limit ex. 8M/8M (upload/download)}';
    protected $description = 'Aplica rate-limit no perfil PPPoE e força reconexão para criar queue dinâmica';

    public function handle(): int
    {
        $username  = $this->argument('username');
        $rateLimit = $this->argument('rate');

        $sites = MikroTikSite::where('active', true)->get();
        if ($sites->isEmpty()) {
            $this->error('Nenhum site MikroTik activo.');
            return 1;
        }

        foreach ($sites as $site) {
            $service = MikroTikService::forSite($site);
            if (! $service->testConnection()['ok']) {
                $this->warn("Site {$site->host}: sem ligação, a ignorar.");
                continue;
            }

            $result = $service->applyRateLimitViaProfile($username, $rateLimit);

            if ($result['ok']) {
                $this->info("✅ [{$site->host}] Perfil '{$result['profile']}' → rate-limit={$rateLimit}");
                $this->info("   Sessão de '{$username}' desconectada — aguarda 10s para reconectar.");
                $this->line("   Depois corre:");
                $this->line("   php artisan mikrotik:sample-bandwidth --debug 2>&1 | grep QUEUES -A12");
                return 0;
            } elseif (str_contains($result['message'], 'não encontrado') && str_contains($result['message'], 'Secret')) {
                $this->warn("⚠️  [{$site->host}] username '{$username}' não encontrado.");
            } else {
                $this->error("❌ [{$site->host}] {$result['message']}");
            }
        }

        $this->error("Username '{$username}' não encontrado em nenhum site.");
        return 1;
    }
}
