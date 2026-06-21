<?php

namespace App\Console\Commands;

use App\Models\MikroTikSite;
use App\Services\MikroTikService;
use Illuminate\Console\Command;

class MikroTikSetRateLimit extends Command
{
    protected $signature = 'mikrotik:set-rate-limit {username : Username PPPoE} {rate : Rate limit ex. 8M/8M (upload/download)}';
    protected $description = 'Aplica rate-limit num secret PPPoE e força reconexão para criar queue dinâmica';

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

            $ok = $service->applySecretRateLimit($username, $rateLimit);
            if ($ok) {
                $this->info("✅ [{$site->host}] rate-limit={$rateLimit} aplicado em '{$username}' e sessão reconectada.");
                $this->line("   Aguarda 5-10 segundos para o cliente reconectar, depois corre:");
                $this->line("   php artisan mikrotik:sample-bandwidth --debug");
                return 0;
            } else {
                $this->warn("⚠️  [{$site->host}] username '{$username}' não encontrado neste site.");
            }
        }

        $this->error("Username '{$username}' não encontrado em nenhum site.");
        return 1;
    }
}
