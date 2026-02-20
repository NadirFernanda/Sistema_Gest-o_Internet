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
        // Be tolerant to casing/spacing of 'estado' and accept empty/null as active
        $planosRaw = Plano::with('cliente')
            ->where(function($q){
                $q->whereRaw("LOWER(TRIM(COALESCE(estado, ''))) LIKE ?", ['%ativ%'])
                  ->orWhereRaw("LOWER(TRIM(COALESCE(estado, ''))) LIKE ?", ['%activ%'])
                  ->orWhereRaw("COALESCE(estado, '') = ''");
            })
            ->get();
        $planos = $planosRaw->filter(function($plano) use ($dias, $hoje) {
            // Determine data de término: preferir proxima_renovacao quando presente
            try {
                if (!empty($plano->proxima_renovacao)) {
                    $dataTermino = Carbon::parse($plano->proxima_renovacao)->startOfDay();
                } elseif (!empty($plano->data_ativacao) && $plano->ciclo) {
                    $cicloInt = intval(preg_replace('/[^0-9]/', '', (string)$plano->ciclo));
                    if ($cicloInt <= 0) {
                        $cicloInt = (int)$plano->ciclo;
                    }
                    $dataTermino = Carbon::parse($plano->data_ativacao)->addDays($cicloInt - 1)->startOfDay();
                } else {
                    $this->info('Ignorado: plano sem data_ativacao, ciclo ou proxima_renovacao. ID: ' . $plano->id);
                    return false;
                }
                $diasRestantes = $hoje->diffInDays($dataTermino, false);
            } catch (\Exception $e) {
                $this->info('Ignorado: erro ao parsear datas do plano ID: ' . $plano->id . ' - ' . $e->getMessage());
                return false;
            }
            $this->info('Plano: ' . ($plano->cliente ? $plano->cliente->nome : '-') . ' | Ativação: ' . $plano->data_ativacao . ' | Ciclo: ' . $plano->ciclo . ' | Término: ' . $dataTermino->toDateString() . ' | DiasRestantes: ' . $diasRestantes . ' | Estado: ' . $plano->estado);
            return $diasRestantes >= 0 && $diasRestantes <= $dias;
        });
        if ($planos->isEmpty()) {
            $this->info('Nenhum plano a vencer nos próximos ' . $dias . ' dias.');
            return 0;
        }
        foreach ($planos as $plano) {
            try {
                if (!empty($plano->proxima_renovacao)) {
                    $dataTermino = Carbon::parse($plano->proxima_renovacao)->startOfDay();
                } else {
                    $cicloInt = intval(preg_replace('/[^0-9]/', '', (string)$plano->ciclo));
                    if ($cicloInt <= 0) { $cicloInt = (int)$plano->ciclo; }
                    $dataTermino = Carbon::parse($plano->data_ativacao)->addDays($cicloInt - 1)->startOfDay();
                }
                $diasRestantes = Carbon::today()->diffInDays($dataTermino, false);
            } catch (\Exception $e) {
                $this->info('Falha ao calcular dataTermino para plano ID: ' . $plano->id . ' - ' . $e->getMessage());
                continue;
            }
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
