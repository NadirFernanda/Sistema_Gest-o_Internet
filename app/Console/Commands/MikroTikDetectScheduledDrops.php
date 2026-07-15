<?php

namespace App\Console\Commands;

use App\Models\MikroTikOnlineStatusEvent;
use App\Models\Plano;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MikroTikDetectScheduledDrops extends Command
{
    protected $signature   = 'mikrotik:detect-scheduled-drops {--dias=14 : Dias de histórico a analisar} {--min-dias=5 : Mínimo de dias distintos com queda na mesma janela horária}';
    protected $description = 'Detecta clientes com quedas que ocorrem sempre no mesmo horário (possível tarefa agendada no router)';

    public function handle(): int
    {
        $dias    = (int) $this->option('dias');
        $minDias = (int) $this->option('min-dias');
        $desde   = now()->subDays($dias);

        $this->info("Analisando quedas dos últimos {$dias} dias (mínimo {$minDias} dias distintos por janela)...");

        $eventos = MikroTikOnlineStatusEvent::with('plano.cliente')
            ->where('event_type', 'offline')
            ->where('occurred_at', '>=', $desde)
            ->whereNotNull('occurred_at')
            ->get();

        if ($eventos->isEmpty()) {
            $this->info('Nenhum evento de queda encontrado.');
            return 0;
        }

        $porPlano = $eventos->groupBy('plano_id');
        $alertas  = [];

        foreach ($porPlano as $planoId => $queda) {
            // Agrupar por janela de 30 min e contar DIAS DISTINTOS com queda.
            // Contar ocorrências totais gera falsos positivos: um cliente instável
            // com 4 quedas/dia acumula 3+ quedas em quase todas as janelas por acaso.
            $diasPorJanela = [];
            foreach ($queda as $evento) {
                $hora   = (int) $evento->occurred_at->format('H');
                $minuto = (int) $evento->occurred_at->format('i');
                $janela = $hora . ':' . ($minuto < 30 ? '00' : '30');
                $dia    = $evento->occurred_at->format('Y-m-d');
                $diasPorJanela[$janela][$dia] = true;
            }

            // Média de dias por janela para este cliente
            $totalJanelas       = count($diasPorJanela);
            $totalDiasAcumulado = array_sum(array_map('count', $diasPorJanela));
            $mediaDias          = $totalJanelas > 0 ? $totalDiasAcumulado / $totalJanelas : 0;

            foreach ($diasPorJanela as $horario => $diasComQueda) {
                $numDias = count($diasComQueda);

                // Critério 1: pelo menos N dias distintos com queda nessa janela
                if ($numDias < $minDias) continue;

                // Critério 2: a janela deve ter pelo menos 2× a média do próprio cliente.
                // Filtra instabilidade geral (onde todas as janelas têm muitos dias).
                if ($mediaDias > 0 && $numDias < $mediaDias * 2.0) continue;

                $percentagem = round($numDias / $dias * 100);
                $plano       = $queda->first()?->plano;
                $cliente     = $plano?->cliente;

                $alertas[] = [
                    'plano_id'    => $planoId,
                    'cliente'     => $cliente?->nome ?? "Plano #{$planoId}",
                    'username'    => $plano?->mikrotik_username ?? '-',
                    'horario'     => $horario,
                    'dias'        => $numDias,
                    'percentagem' => $percentagem,
                    'total_quedas'=> $queda->count(),
                ];

                Log::warning('MikroTik: padrão de queda em horário fixo detectado', [
                    'plano_id'       => $planoId,
                    'cliente'        => $cliente?->nome,
                    'username'       => $plano?->mikrotik_username,
                    'horario'        => $horario,
                    'dias_distintos' => $numDias,
                    'percentagem'    => $percentagem . '%',
                    'dias_analisados'=> $dias,
                ]);
            }
        }

        if (empty($alertas)) {
            $this->info("Nenhum padrão de queda em horário fixo detectado.");
            return 0;
        }

        usort($alertas, fn($a, $b) => $b['percentagem'] <=> $a['percentagem']);

        $this->newLine();
        $this->warn("⚠  " . count($alertas) . " padrão(ões) de queda em horário fixo:");
        $this->newLine();

        $this->table(
            ['Cliente', 'Username', 'Horário', 'Dias com queda', '% dos dias'],
            array_map(fn($a) => [
                $a['cliente'],
                $a['username'],
                $a['horario'],
                $a['dias'] . 'x',
                $a['percentagem'] . '%',
            ], $alertas)
        );

        $this->newLine();
        $this->warn("Causa provável: tarefa agendada (Scheduler) no router MikroTik.");
        $this->warn("Solução: verificar System → Scheduler no WinBox e remover regras no horário indicado.");

        return 0;
    }
}
