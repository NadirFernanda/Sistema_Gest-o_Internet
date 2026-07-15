<?php

namespace App\Console\Commands;

use App\Models\MikroTikOnlineStatusEvent;
use App\Models\Plano;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MikroTikDetectScheduledDrops extends Command
{
    protected $signature   = 'mikrotik:detect-scheduled-drops {--dias=14 : Dias de histórico a analisar} {--min-ocorrencias=3 : Mínimo de quedas no mesmo horário para alertar}';
    protected $description = 'Detecta clientes com quedas que ocorrem sempre no mesmo horário (possível tarefa agendada no router)';

    public function handle(): int
    {
        $dias          = (int) $this->option('dias');
        $minOcorrencias = (int) $this->option('min-ocorrencias');
        $desde         = now()->subDays($dias);

        $this->info("Analisando quedas dos últimos {$dias} dias...");

        // Buscar todos os eventos de queda com hora registada
        $eventos = MikroTikOnlineStatusEvent::with('plano.cliente')
            ->where('event_type', 'offline')
            ->where('occurred_at', '>=', $desde)
            ->whereNotNull('occurred_at')
            ->get();

        if ($eventos->isEmpty()) {
            $this->info('Nenhum evento de queda encontrado.');
            return 0;
        }

        // Agrupar por plano e depois por hora (janela de 30 minutos)
        $porPlano = $eventos->groupBy('plano_id');
        $alertas  = [];

        foreach ($porPlano as $planoId => $queda) {
            if ($queda->count() < $minOcorrencias) {
                continue;
            }

            // Contar ocorrências por janela de 30 minutos (ex: 23:00-23:30)
            $porHorario = [];
            foreach ($queda as $evento) {
                $hora   = (int) $evento->occurred_at->format('H');
                $minuto = (int) $evento->occurred_at->format('i');
                // Arredondar para janela de 30 min
                $janela = $hora . ':' . ($minuto < 30 ? '00' : '30');
                $porHorario[$janela] = ($porHorario[$janela] ?? 0) + 1;
            }

            // Verificar se algum horário tem quedas repetidas suficientes
            foreach ($porHorario as $horario => $contagem) {
                if ($contagem >= $minOcorrencias) {
                    $plano   = $queda->first()?->plano;
                    $cliente = $plano?->cliente;
                    $alertas[] = [
                        'plano_id'   => $planoId,
                        'cliente'    => $cliente?->nome ?? "Plano #{$planoId}",
                        'username'   => $plano?->mikrotik_username ?? '-',
                        'horario'    => $horario,
                        'ocorrencias'=> $contagem,
                        'total_quedas' => $queda->count(),
                    ];

                    Log::warning('MikroTik: padrão de queda em horário fixo detectado', [
                        'plano_id'    => $planoId,
                        'cliente'     => $cliente?->nome,
                        'username'    => $plano?->mikrotik_username,
                        'horario'     => $horario,
                        'ocorrencias' => $contagem,
                        'dias'        => $dias,
                    ]);
                }
            }
        }

        if (empty($alertas)) {
            $this->info("Nenhum padrão de queda em horário fixo detectado.");
            return 0;
        }

        $this->newLine();
        $this->warn("⚠  " . count($alertas) . " cliente(s) com padrão de queda em horário fixo:");
        $this->newLine();

        $this->table(
            ['Cliente', 'Username', 'Horário', 'Ocorrências', 'Total Quedas'],
            array_map(fn($a) => [
                $a['cliente'],
                $a['username'],
                $a['horario'],
                $a['ocorrencias'] . 'x',
                $a['total_quedas'],
            ], $alertas)
        );

        $this->newLine();
        $this->warn("Causa provável: tarefa agendada (Scheduler) no router MikroTik.");
        $this->warn("Solução: verificar System → Scheduler no WinBox e remover regras no horário indicado.");

        return 0;
    }
}
