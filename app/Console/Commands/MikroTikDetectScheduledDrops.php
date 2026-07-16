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

    /**
     * Analisa eventos de queda para um conjunto de plano_ids e retorna padrões detectados.
     * Usado tanto pelo comando CLI como pela UI (página do cliente).
     *
     * @param  int[]  $planoIds
     * @return array  [ ['plano_id', 'username', 'horario', 'dias', 'percentagem', 'total_quedas'], ... ]
     */
    public static function analisarPlanos(array $planoIds, int $dias = 14, int $minDias = 5): array
    {
        if (empty($planoIds)) {
            return [];
        }

        $desde = now()->subDays($dias);

        $eventos = MikroTikOnlineStatusEvent::with('plano')
            ->whereIn('plano_id', $planoIds)
            ->where('event_type', 'offline')
            ->where('occurred_at', '>=', $desde)
            ->whereNotNull('occurred_at')
            ->get();

        if ($eventos->isEmpty()) {
            return [];
        }

        $porPlano = $eventos->groupBy('plano_id');
        $alertas  = [];

        foreach ($porPlano as $planoId => $queda) {
            $diasPorJanela = [];
            foreach ($queda as $evento) {
                $hora   = (int) $evento->occurred_at->format('H');
                $minuto = (int) $evento->occurred_at->format('i');
                $janela = $hora . ':' . ($minuto < 30 ? '00' : '30');
                $dia    = $evento->occurred_at->format('Y-m-d');
                $diasPorJanela[$janela][$dia] = true;
            }

            $totalJanelas       = count($diasPorJanela);
            $totalDiasAcumulado = array_sum(array_map('count', $diasPorJanela));
            $mediaDias          = $totalJanelas > 0 ? $totalDiasAcumulado / $totalJanelas : 0;

            foreach ($diasPorJanela as $horario => $diasComQueda) {
                $numDias = count($diasComQueda);
                if ($numDias < $minDias) continue;
                if ($mediaDias > 0 && $numDias < $mediaDias * 2.0) continue;

                $alertas[] = [
                    'plano_id'    => $planoId,
                    'username'    => $queda->first()?->plano?->mikrotik_username ?? '-',
                    'horario'     => $horario,
                    'dias'        => $numDias,
                    'percentagem' => round($numDias / $dias * 100),
                    'total_quedas'=> $queda->count(),
                ];
            }
        }

        return $alertas;
    }

    /**
     * Detecta quedas simultâneas: várias ligações PPPoE que caem ao mesmo tempo.
     * Quando N clientes caem na mesma janela de 5 minutos em múltiplos dias,
     * o problema é no router do ISP (Scheduler, reinício de serviço, energia), não nos clientes.
     *
     * @return array [ ['horario', 'ocorrencias', 'max_clientes', 'avg_clientes', 'planos_exemplo'], ... ]
     */
    public static function analisarSimultaneos(int $dias = 30, int $minClientes = 3, int $minOcorrencias = 3): array
    {
        $desde = now()->subDays($dias);

        $eventos = MikroTikOnlineStatusEvent::with('plano.cliente')
            ->where('event_type', 'offline')
            ->where('occurred_at', '>=', $desde)
            ->whereNotNull('occurred_at')
            ->get();

        if ($eventos->isEmpty()) {
            return [];
        }

        // Agrupar por janela de 5 minutos (dia + horário)
        $byJanelaDia = []; // [horario => [dia => [plano_id => cliente_nome]]]
        foreach ($eventos as $evento) {
            $h = (int) $evento->occurred_at->format('H');
            $m = (int) $evento->occurred_at->format('i');
            $mRound = (int) (floor($m / 5) * 5);
            $janela = sprintf('%02d:%02d', $h, $mRound);
            $dia    = $evento->occurred_at->format('Y-m-d');
            $byJanelaDia[$janela][$dia][$evento->plano_id] = $evento->plano?->cliente?->nome ?? "Plano #{$evento->plano_id}";
        }

        $alertas = [];
        foreach ($byJanelaDia as $horario => $diasData) {
            // Contar dias onde >= $minClientes clientes caíram ao mesmo tempo
            $diasSimultaneos = array_filter(
                $diasData,
                fn($planos) => count($planos) >= $minClientes
            );

            if (count($diasSimultaneos) < $minOcorrencias) {
                continue;
            }

            $numClientes = array_map('count', $diasSimultaneos);
            // Recolher todos os planos únicos afectados para exemplo
            $todosPlanos = [];
            foreach ($diasSimultaneos as $planos) {
                foreach ($planos as $planoId => $nome) {
                    $todosPlanos[$planoId] = $nome;
                }
            }

            $alertas[] = [
                'horario'       => $horario,
                'ocorrencias'   => count($diasSimultaneos),
                'max_clientes'  => max($numClientes),
                'avg_clientes'  => round(array_sum($numClientes) / count($numClientes), 1),
                'planos_exemplo'=> array_slice(array_values($todosPlanos), 0, 5),
                'total_planos'  => count($todosPlanos),
            ];
        }

        usort($alertas, fn($a, $b) => $b['ocorrencias'] <=> $a['ocorrencias']);

        return $alertas;
    }

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

        // ── Análise de quedas simultâneas ──────────────────────────────────────
        $this->newLine();
        $this->info("Analisando quedas simultâneas (problema no router do ISP)...");
        $simultaneos = self::analisarSimultaneos(dias: $dias, minClientes: 3, minOcorrencias: 2);

        if (empty($simultaneos)) {
            $this->info("Nenhuma queda simultânea relevante detectada.");
        } else {
            $this->newLine();
            $this->error("🔴  " . count($simultaneos) . " horário(s) com quedas simultâneas de múltiplos clientes (causa no ROUTER DO ISP):");
            $this->newLine();
            $this->table(
                ['Horário', 'Ocorrências (dias)', 'Máx. clientes', 'Média clientes', 'Clientes afectados (ex.)'],
                array_map(fn($a) => [
                    $a['horario'],
                    $a['ocorrencias'] . 'x',
                    $a['max_clientes'],
                    $a['avg_clientes'],
                    implode(', ', $a['planos_exemplo']) . ($a['total_planos'] > 5 ? ' (+' . ($a['total_planos'] - 5) . ')' : ''),
                ], $simultaneos)
            );
            $this->newLine();
            $this->error("ATENÇÃO: Múltiplos clientes a cair ao mesmo tempo = problema no router do ISP.");
            $this->warn("Verificar: Scheduler, reinícios de serviço PPP, problemas de energia no local do router.");
        }

        return 0;
    }
}
