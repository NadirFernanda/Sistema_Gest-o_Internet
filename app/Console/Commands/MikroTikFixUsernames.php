<?php

namespace App\Console\Commands;

use App\Models\MikroTikSite;
use App\Models\Plano;
use App\Services\MikroTikService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MikroTikFixUsernames extends Command
{
    protected $signature = 'mikrotik:fix-usernames
                            {--site= : ID do site (omitir = todos os sites activos)}
                            {--dry-run : Mostrar correspondências sem gravar na BD}
                            {--list-secrets : Listar todos os secrets do router com plano associado (para matching manual)}';

    protected $description = 'Associa mikrotik_username nos planos encontrando o secret correcto no router pelo nº de telemóvel';

    public function handle(): int
    {
        $dryRun      = $this->option('dry-run');
        $listSecrets = $this->option('list-secrets');
        $siteId      = $this->option('site');

        $sites = $siteId
            ? MikroTikSite::where('id', $siteId)->get()
            : MikroTikSite::where('active', true)->get();

        if ($sites->isEmpty()) {
            $this->warn('Nenhum site MikroTik activo encontrado.');
            return self::SUCCESS;
        }

        foreach ($sites as $site) {
            $this->info("\n=== Site: {$site->nome} ({$site->host}) ===");

            $service = MikroTikService::forSite($site);
            $test    = $service->testConnection();

            if (! $test['ok']) {
                $this->warn("Router inacessível: " . ($test['error'] ?? 'sem resposta'));
                continue;
            }

            // Recolher todos os secrets do router indexados por nome
            $secrets       = $service->listSecrets();
            $secretsByName = [];
            foreach ($secrets as $s) {
                $name = $s['name'] ?? $s['=name'] ?? '';
                if ($name !== '') {
                    $secretsByName[$name] = $s;
                }
            }

            $this->line('Secrets no router: ' . count($secretsByName));

            // Índice de usernames já usados por algum plano neste site
            $usedUsernames = Plano::whereHas('cliente', fn($q) => $q->where('mikrotik_site_id', $site->id))
                ->whereNotNull('mikrotik_username')
                ->pluck('mikrotik_username')
                ->flip(); // username => true

            if ($listSecrets) {
                $this->info("\nSecrets no router — coluna: nome | disabled | profile | comment");
                foreach ($secretsByName as $name => $s) {
                    $disabled = $s['disabled'] ?? $s['=disabled'] ?? '?';
                    $profile  = $s['profile']  ?? $s['=profile']  ?? '?';
                    $comment  = $s['comment']  ?? $s['=comment']  ?? '';
                    $linked   = isset($usedUsernames[$name]) ? ' [SGA ✓]' : ' [SEM PLANO]';
                    $this->line("  {$name} | disabled={$disabled} | profile={$profile} | {$comment}{$linked}");
                }
                $this->line('');
            }

            // Todos os planos activos/suspensos para clientes deste site
            $planos = Plano::with('cliente')
                ->whereHas('cliente', fn($q) => $q->where('mikrotik_site_id', $site->id))
                ->whereIn('estado', ['Ativo', 'Em aviso', 'Suspenso'])
                ->get();

            $alreadyOk = 0;
            $fixed     = 0;
            $notFound  = [];

            foreach ($planos as $plano) {
                $cliente = $plano->cliente;
                $phone   = preg_replace('/\D/', '', $cliente->contato ?? '');

                // 1. mikrotik_username já definido e existe no router → correcto
                if ($plano->mikrotik_username && isset($secretsByName[$plano->mikrotik_username])) {
                    $alreadyOk++;
                    continue;
                }

                // Tentar encontrar o secret correcto
                $found = $this->findMatch($plano->id, $phone, $site->user_prefix ?? '', $secretsByName);

                if ($found !== null) {
                    $old = $plano->mikrotik_username ?? '(null)';
                    $this->line("  ✓ Plano #{$plano->id} | {$cliente->nome}");
                    $this->line("    {$old} → {$found}" . ($dryRun ? ' [dry-run, não gravado]' : ''));

                    if (! $dryRun) {
                        $plano->mikrotik_username  = $found;
                        $plano->mikrotik_synced_at = null; // forçar re-sync pelo sync-plans
                        $plano->saveQuietly();

                        Log::info('mikrotik:fix-usernames: username corrigido', [
                            'plano_id' => $plano->id,
                            'cliente'  => $cliente->nome,
                            'old'      => $old,
                            'new'      => $found,
                            'site'     => $site->nome,
                        ]);
                    }
                    $fixed++;
                } else {
                    $notFound[] = "Plano #{$plano->id} | {$cliente->nome} | tel: {$phone}";
                }
            }

            $this->info("Resultado: {$alreadyOk} já correctos, {$fixed} " . ($dryRun ? 'encontrados (dry-run)' : 'corrigidos') . ', ' . count($notFound) . ' sem correspondência.');

            if (! empty($notFound)) {
                $this->warn('Sem correspondência (verificar manualmente no WinBox):');
                foreach ($notFound as $line) {
                    $this->warn("  ✗ {$line}");
                }
            }
        }

        if (! $dryRun) {
            $this->info("\nPode agora correr: php artisan mikrotik:sync-plans");
            $this->info("para sincronizar perfis e estado disabled/enabled no router.");
        }

        return self::SUCCESS;
    }

    /**
     * Tentar encontrar o nome do secret no router que corresponde a este plano/cliente.
     *
     * Prioridade:
     *   1. Comment do secret contém "SGA#<plano_id>|" (criado pelo nosso sistema)
     *   2. Nome do secret == phone (ex: 923456789)
     *   3. Nome do secret == user_prefix + phone (ex: sgaw-923456789)
     *   4. Nome do secret começa com o phone (phone é sufixo do nome)
     *
     * Não fazemos substring genérica para evitar falsos positivos.
     */
    private function findMatch(int $planoId, string $phone, string $prefix, array $secretsByName): ?string
    {
        // 1. Comment gerado pelo nosso sistema (mais fiável)
        foreach ($secretsByName as $name => $s) {
            $comment = $s['comment'] ?? $s['=comment'] ?? '';
            if (str_contains($comment, "SGA#{$planoId}|")) {
                return $name;
            }
        }

        if ($phone === '') return null;

        // 2. Exactamente o número de telefone
        if (isset($secretsByName[$phone])) {
            return $phone;
        }

        // 3. Prefix + phone
        if ($prefix !== '' && isset($secretsByName[$prefix . $phone])) {
            return $prefix . $phone;
        }

        // 4. O nome do secret termina com o phone (ex: "244923456789" ou "0923456789")
        foreach ($secretsByName as $name => $s) {
            if (str_ends_with($name, $phone) && strlen($name) <= strlen($phone) + 4) {
                return $name;
            }
        }

        return null;
    }
}
