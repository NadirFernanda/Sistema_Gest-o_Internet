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
use App\Models\ClienteEquipamento;
use App\Models\EstoqueEquipamento;
use App\Models\PlanTemplate;
use App\Models\User;
use App\Models\Alerta;
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

            if (class_exists(Alerta::class)) {
                $alertas = Alerta::whereBetween('created_at', [$start, $end])->get();
            } elseif (class_exists(Alert::class)) {
                $alertas = Alert::whereBetween('created_at', [$start, $end])->get();
            } else {
                $alertas = collect();
            }

            $clienteEquipamentos = ClienteEquipamento::whereBetween('created_at', [$start, $end])->get();
            $estoque = EstoqueEquipamento::whereBetween('created_at', [$start, $end])->get();
            $planTemplates = PlanTemplate::whereBetween('created_at', [$start, $end])->get();
            $users = User::whereBetween('created_at', [$start, $end])->get();

            $export = (new RelatorioMultiAbaExport($cobrancas, $clientes, $planos, $equipamentos, $alertas))
                ->withClienteEquipamentos($clienteEquipamentos)
                ->withEstoque($estoque)
                ->withPlanTemplates($planTemplates)
                ->withUsers($users);

            Excel::store($export, 'relatorios/' . $filename, 'local');

            Log::info("Relatorio gerado: {$filename}");
            $this->info("Relatório salvo: storage/app/relatorios/{$filename}");
        } catch (\Exception $e) {
            Log::error('Erro gerando relatório geral: ' . $e->getMessage());
            $this->error('Erro ao gerar relatório: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
