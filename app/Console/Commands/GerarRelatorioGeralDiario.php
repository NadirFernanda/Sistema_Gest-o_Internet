<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cobranca;
use App\Exports\RelatorioMultiAbaExport;
use App\Models\Cliente;
use App\Models\Plano;
use App\Models\Equipamento;
use App\Models\EstoqueEquipamento;
use App\Models\ClienteEquipamento;
use App\Models\PlanTemplate;
use App\Models\User;
use App\Models\DeletionAudit;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class GerarRelatorioGeralDiario extends Command
{
    protected $signature = 'relatorio:geral-diario {--field=data_vencimento : Campo de data para filtrar (data_vencimento|data_pagamento)} {--date= : Data (Y-m-d) para o relatório diário} {--last-hours= : Intervalo em horas para incluir registros recentes (ex: 24)}';
    protected $description = 'Gera e envia o relatório geral diário (multi-aba) do sistema';

    public function handle()
    {
        $field = $this->option('field') ?? 'data_vencimento';
        $dateInput = $this->option('date');
        $lastHours = $this->option('last-hours');

        if ($lastHours && is_numeric($lastHours) && (int)$lastHours > 0) {
            $now = Carbon::now();
            $start = Carbon::now()->subHours((int)$lastHours);
            $cobrancas = Cobranca::whereBetween($field, [$start, $now])->get();
            if ($cobrancas->isEmpty()) {
                $cobrancas = Cobranca::whereBetween('created_at', [$start, $now])
                    ->orWhereBetween('data_vencimento', [$start, $now])
                    ->orWhereBetween('data_pagamento', [$start, $now])
                    ->get();
            }

            $clientes = Cliente::whereBetween('created_at', [$start, $now])->get();
            $planos = Plano::whereBetween('created_at', [$start, $now])->get();
            $equipamentos = Equipamento::whereBetween('created_at', [$start, $now])->get();
            $estoque = EstoqueEquipamento::whereBetween('created_at', [$start, $now])->get();
            $clienteEquipamentos = ClienteEquipamento::whereBetween('created_at', [$start, $now])->get();
            $planTemplates = PlanTemplate::whereBetween('created_at', [$start, $now])->get();
            $users = User::whereBetween('created_at', [$start, $now])->get();
            $deletionAudits = DeletionAudit::whereBetween('created_at', [$start, $now])->get();
            $fileName = 'relatorio_geral_ultimas_' . (int)$lastHours . 'h_' . $now->format('Y_m_d_His') . '.xlsx';
        } else {
            $hoje = $dateInput ? Carbon::parse($dateInput)->startOfDay() : Carbon::today();
            $cobrancas = Cobranca::whereDate($field, $hoje)->get();
            if ($cobrancas->isEmpty()) {
                $cobrancas = Cobranca::whereDate('created_at', $hoje)
                    ->orWhereDate('data_vencimento', $hoje)
                    ->orWhereDate('data_pagamento', $hoje)
                    ->get();
            }
            $clientes = Cliente::whereDate('created_at', $hoje)->get();
            $planos = Plano::whereDate('created_at', $hoje)->get();
            $equipamentos = Equipamento::whereDate('created_at', $hoje)->get();
            $estoque = EstoqueEquipamento::whereDate('created_at', $hoje)->get();
            $clienteEquipamentos = ClienteEquipamento::whereDate('created_at', $hoje)->get();
            $planTemplates = PlanTemplate::whereDate('created_at', $hoje)->get();
            $users = User::whereDate('created_at', $hoje)->get();
            $deletionAudits = DeletionAudit::whereDate('created_at', $hoje)->get();
            $fileName = 'relatorio_geral_diario_' . $hoje->format('Y_m_d') . '.xlsx';
        }

        $alertas = Plano::with('cliente')
            ->where('estado', 'Ativo')
            ->get()
            ->filter(function($plano) {
                if (!$plano->data_ativacao || !$plano->ciclo) return false;
                $dataTermino = \Carbon\Carbon::parse($plano->data_ativacao)->addDays($plano->ciclo - 1)->startOfDay();
                $diasRestantes = \Carbon\Carbon::today()->diffInDays($dataTermino, false);
                return $diasRestantes >= 0 && $diasRestantes <= 7;
            });

        $filePath = 'relatorios/' . $fileName;
        try {
            if (!Storage::exists('relatorios')) {
                Storage::makeDirectory('relatorios');
            }
            $export = (new RelatorioMultiAbaExport($cobrancas, $clientes, $planos, $equipamentos, $alertas))
                ->withClienteEquipamentos($clienteEquipamentos)
                ->withEstoque($estoque)
                ->withPlanTemplates($planTemplates)
                    ->withUsers($users)
                    ->withDeletionAudits($deletionAudits);
            Excel::store($export, $filePath);
            $this->info('Relatório geral multi-aba gerado (disk local): ' . $filePath);

            $src = Storage::disk('local')->path($filePath);
            $dst = storage_path('app/' . $filePath);
            if (!file_exists(dirname($dst))) {
                mkdir(dirname($dst), 0755, true);
            }
            if (file_exists($src)) {
                copy($src, $dst);
                $this->info('Relatório copiado para: ' . $dst);
            } else {
                $this->warn('Arquivo gerado não encontrado em disk local: ' . $src);
            }

            $email = config('mail.from.address');
            if ($email) {
                Mail::raw('Segue em anexo o Relatório Geral do sistema.', function ($message) use ($email, $dst, $fileName) {
                    $message->to($email)
                            ->subject('Relatório Geral do Sistema')
                            ->attach($dst, [
                                'as' => $fileName,
                                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            ]);
                });
                $this->info('Relatório enviado para: ' . $email);
            }
        } catch (\Exception $e) {
            \Log::error('Falha ao gerar/enviar relatório geral diário: ' . $e->getMessage(), ['exception' => $e]);
            $this->error('Erro ao gerar relatório geral diário: ' . $e->getMessage());
            return 1;
        }
        return 0;
    }
}
