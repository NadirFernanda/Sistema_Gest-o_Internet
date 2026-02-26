<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cobranca;
use Carbon\Carbon;
use App\Notifications\ClienteDevolucaoEquipamentoEmail;
use App\Notifications\ClienteDevolucaoEquipamentoWhatsApp;
use App\Models\ClienteEquipamento;
use App\Jobs\WriteAuditLogJob;
use Illuminate\Support\Facades\Log;

class EnviarNotificacaoDevolucao extends Command
{
    protected $signature = 'notificacao:devolucao';

    protected $description = 'Enviar aviso de devolução de equipamento para cobrancas vencidas há mais de 30 dias e sem pagamento';

    public function handle()
    {
        $limite = Carbon::now()->subDays(30)->startOfDay();

        $cobrancas = Cobranca::whereNull('data_pagamento')
            ->where('data_vencimento', '<=', $limite)
            ->get();

        $this->info('Cobrancas encontradas: ' . $cobrancas->count());

        foreach ($cobrancas as $cobranca) {
            $cliente = $cobranca->cliente;
            if (!$cliente) {
                Log::warning("Cobranca {$cobranca->id} sem cliente associado");
                continue;
            }
            try {
                // Marca equipamentos como "devolução solicitada" quando aplicável
                $equipamentos = $cliente->clienteEquipamentos()->where('status', ClienteEquipamento::STATUS_EMPRESTADO)->get();
                foreach ($equipamentos as $ce) {
                    $before = $ce->toArray();
                    $ce->update([
                        'status' => ClienteEquipamento::STATUS_DEVOLUCAO_SOLICITADA,
                        'devolucao_solicitada_at' => Carbon::now(),
                        'devolucao_prazo' => Carbon::now()->addDays(7)->toDateString(),
                        'motivo_requisicao' => 'Inadimplência > 30 dias',
                    ]);

                    // enqueue audit
                    WriteAuditLogJob::dispatch([
                        'resource_type' => 'ClienteEquipamento',
                        'resource_id' => $ce->id,
                        'action' => 'marcar_devolucao_solicitada',
                        'payload_before' => $before,
                        'payload_after' => $ce->toArray(),
                        'actor_name' => 'system',
                        'module' => 'notificacao_devolucao',
                    ]);
                }

                $cliente->notify(new ClienteDevolucaoEquipamentoEmail($cliente));
                $cliente->notify(new ClienteDevolucaoEquipamentoWhatsApp($cliente));
                Log::info("Notificações de devolução enviadas e equipamentos marcados para cliente {$cliente->id}");
            } catch (\Exception $e) {
                Log::error("Erro ao enviar notificações para cliente {$cliente->id}: " . $e->getMessage());
            }
        }

        return 0;
    }
}
