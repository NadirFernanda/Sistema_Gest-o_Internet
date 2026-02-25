<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cobranca;
use Carbon\Carbon;
use App\Notifications\ClienteDevolucaoEquipamentoEmail;
use App\Notifications\ClienteDevolucaoEquipamentoWhatsApp;
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
                $cliente->notify(new ClienteDevolucaoEquipamentoEmail($cliente));
                $cliente->notify(new ClienteDevolucaoEquipamentoWhatsApp($cliente));
                Log::info("Notificações de devolução enviadas para cliente {$cliente->id}");
            } catch (\Exception $e) {
                Log::error("Erro ao enviar notificações para cliente {$cliente->id}: " . $e->getMessage());
            }
        }

        return 0;
    }
}
