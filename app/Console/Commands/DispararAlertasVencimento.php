<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Plano;
use Carbon\Carbon;

class DispararAlertasVencimento extends Command
{
    protected $signature = 'alertas:disparar {--dias=5 : Dias para vencimento}';
    protected $description = 'Dispara alertas de vencimento por e-mail e WhatsApp para planos próximos do vencimento';

    public function handle()
    {
        $dias = (int) $this->option('dias');
        $hoje = Carbon::today(); // apenas a data, sem hora
        $planosRaw = Plano::with('cliente')
            ->where('estado', 'Ativo')
            ->get();
        $planos = $planosRaw->filter(function($plano) use ($dias, $hoje) {
            if (!$plano->data_ativacao || !$plano->ciclo) {
                $this->info('Ignorado: plano sem data_ativacao ou ciclo. ID: ' . $plano->id);
                return false;
            }
            // Corrigir: data de término deve ser inclusiva, subtrai 1 do ciclo
            $dataTermino = Carbon::parse($plano->data_ativacao)->addDays($plano->ciclo - 1)->startOfDay();
            $diasRestantes = $hoje->diffInDays($dataTermino, false);
            $this->info('Plano: ' . ($plano->cliente ? $plano->cliente->nome : '-') . ' | Ativação: ' . $plano->data_ativacao . ' | Ciclo: ' . $plano->ciclo . ' | Término: ' . $dataTermino->toDateString() . ' | DiasRestantes: ' . $diasRestantes . ' | Estado: ' . $plano->estado);
            return $diasRestantes >= 0 && $diasRestantes <= $dias;
        });
        if ($planos->isEmpty()) {
            $this->info('Nenhum plano a vencer nos próximos ' . $dias . ' dias.');
            return 0;
        }
        foreach ($planos as $plano) {
            $dataTermino = Carbon::parse($plano->data_ativacao)->addDays($plano->ciclo - 1)->startOfDay();
            $diasRestantes = Carbon::today()->diffInDays($dataTermino, false);
            if ($plano->cliente) {
                $plano->cliente->notify(new \App\Notifications\ClienteVencimentoAlert($plano->cliente, $plano, $diasRestantes));
                $plano->cliente->notify(new \App\Notifications\ClienteVencimentoWhatsApp($plano->cliente, $plano, $diasRestantes));
                $this->info('Alerta enviado para: ' . $plano->cliente->email . ' / ' . $plano->cliente->contato . ' (diasRestantes: ' . $diasRestantes . ')');
            }
        }
        $this->info('Alertas disparados com sucesso!');
        return 0;
    }
}
