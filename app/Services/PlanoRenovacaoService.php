<?php

namespace App\Services;

use App\Models\Cobranca;
use App\Models\Plano;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PlanoRenovacaoService
{
    /**
     * Avança a proxima_renovacao do plano associado a esta cobrança
     * e reactiva o utilizador no MikroTik se estava suspenso.
     * Chamado tanto pelo webhook Pay4All como por pagamentos manuais.
     */
    public static function avancarPlano(Cobranca $cobranca): void
    {
        $cliente = $cobranca->cliente()->with('mikrotikSite')->first();
        if (! $cliente) return;

        $planos = Plano::where('cliente_id', $cliente->id)
            ->whereIn('estado', ['Ativo', 'Em aviso', 'Suspenso'])
            ->whereNotNull('proxima_renovacao')
            ->whereNotNull('ciclo')
            ->orderBy('proxima_renovacao', 'asc')
            ->get();

        if ($planos->isEmpty()) {
            Log::warning('PlanoRenovacaoService: nenhum plano activo encontrado', [
                'cliente_id'  => $cliente->id,
                'cobranca_id' => $cobranca->id,
            ]);
            return;
        }

        // Match pelo data_vencimento da cobrança (janela de ±5 dias)
        $plano = null;
        if ($cobranca->data_vencimento) {
            $vencimento = Carbon::parse($cobranca->data_vencimento);
            $plano = $planos->first(fn($p) =>
                Carbon::parse($p->proxima_renovacao)->diffInDays($vencimento, false) >= -5
                && Carbon::parse($p->proxima_renovacao)->diffInDays($vencimento, false) <= 5
            );
        }

        $plano = $plano ?? $planos->first();

        $ciclo = (int) $plano->ciclo;
        if ($ciclo <= 0) return;

        $estadoAnterior = $plano->estado;
        // Se proxima_renovacao já está no passado, parte de hoje para garantir
        // que o novo prazo fica no futuro e o expire-plans não suspende imediatamente
        $base          = Carbon::parse($plano->proxima_renovacao)->max(Carbon::today());
        $novaRenovacao = $base->addDays($ciclo);

        $plano->proxima_renovacao = $novaRenovacao->toDateString();
        if (in_array($plano->estado, ['Suspenso', 'Em aviso'])) {
            $plano->estado = 'Ativo';
        }
        $plano->saveQuietly();

        Log::info('PlanoRenovacaoService: proxima_renovacao avançada', [
            'cobranca_id'     => $cobranca->id,
            'plano_id'        => $plano->id,
            'cliente'         => $cliente->nome,
            'estado_anterior' => $estadoAnterior,
            'nova_renovacao'  => $novaRenovacao->toDateString(),
        ]);

        if (in_array($estadoAnterior, ['Suspenso', 'Em aviso']) && $cliente->mikrotikSite) {
            try {
                MikroTikService::forSite($cliente->mikrotikSite)->activateUser($plano->fresh());
                Log::info('PlanoRenovacaoService: utilizador reactivado no MikroTik', ['plano_id' => $plano->id]);
            } catch (\Throwable $e) {
                Log::warning('PlanoRenovacaoService: falha ao reactivar MikroTik', [
                    'plano_id' => $plano->id,
                    'error'    => $e->getMessage(),
                ]);
            }
        }
    }
}
