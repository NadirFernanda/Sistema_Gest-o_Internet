<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RelatorioMultiAbaExport;
use App\Models\Cobranca;
use App\Models\Cliente;
use App\Models\Plano;
use App\Models\Equipamento;
use App\Models\Alert;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RelatorioGeralCommand extends Command
{
    protected $signature = 'relatorio:geral {--period=daily}';

    protected $description = 'Gera relatório geral (daily|weekly|monthly) e salva em storage/app/relatorios';

    public function handle()
    {
        $period = $this->option('period') ?? 'daily';

        $now = Carbon::now();
        if ($period === 'weekly') {
            $start = $now->copy()->startOfWeek();
            $end = $now->copy()->endOfWeek();
            $label = $start->format('Y_m_d') . '_a_' . $end->format('Y_m_d');
            $filename = "relatorio_geral_semanal_{$label}.xlsx";
        } elseif ($period === 'monthly') {
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();
            $label = $now->format('Y_m');
            $filename = "relatorio_geral_mensal_{$label}.xlsx";
        } else {
            // daily
            use App\Models\Alerta;
            $start = $now->copy()->subDay();
            $end = $now->copy();
            $label = $now->format('Y_m_d_His');
            $filename = "relatorio_geral_ultimas_24h_{$label}.xlsx";
        }

        try {
            $cobrancas = Cobranca::whereBetween('created_at', [$start, $end])->get();
            $clientes = Cliente::whereBetween('created_at', [$start, $end])->get();
            $planos = Plano::whereBetween('created_at', [$start, $end])->get();
            $equipamentos = Equipamento::whereBetween('created_at', [$start, $end])->get();
            // Alert model may have different name; try App\Models\Alerta or App\Models\Alert
            if (class_exists(\App\Models\Alerta::class)) {
                $alertas = \App\Models\Alerta::whereBetween('created_at', [$start, $end])->get();
            } elseif (class_exists(\App\Models\Alert::class)) {
                $alertas = \App\Models\Alert::whereBetween('created_at', [$start, $end])->get();
            } else {
                $alertas = collect();
            }

            $export = new RelatorioMultiAbaExport($cobrancas, $clientes, $planos, $equipamentos, $alertas);

            Excel::store($export, 'relatorios/' . $filename, 'local');

            Log::info("Relatorio gerado: {$filename}");
            $this->info("Relatório salvo: storage/app/relatorios/{$filename}");
        } catch (\Exception $e) {
            Log::error('Erro gerando relatório geral: ' . $e->getMessage());
            $this->error('Erro ao gerar relatório: ' . $e->getMessage());
                        // Alert model may have different name; try App\Models\Alerta or App\Models\Alert
                        if (class_exists(Alerta::class)) {
                            $alertas = Alerta::whereBetween('created_at', [$start, $end])->get();
                        } elseif (class_exists(Alert::class)) {
                            $alertas = Alert::whereBetween('created_at', [$start, $end])->get();
                        } else {
                            $alertas = collect();
                        }
