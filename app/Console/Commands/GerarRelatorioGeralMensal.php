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

class GerarRelatorioGeralMensal extends Command
{
    protected $signature = 'relatorio:geral-mensal {--field=data_vencimento : Campo de data para filtrar (data_vencimento|data_pagamento)}';
    protected $description = 'Gera e envia o relatório geral mensal (multi-aba) do sistema';

    public function handle()
    {
        $inicio = Carbon::now()->startOfMonth();
        $fim = Carbon::now()->endOfMonth();
        $field = $this->option('field') ?? 'data_vencimento';
        $cobrancas = Cobranca::whereBetween($field, [$inicio, $fim])->get();
        if ($cobrancas->isEmpty()) {
            $cobrancas = Cobranca::whereBetween('created_at', [$inicio, $fim])
                ->orWhereBetween('data_vencimento', [$inicio, $fim])
                ->orWhereBetween('data_pagamento', [$inicio, $fim])
                ->get();
        }
        $clientes = Cliente::whereBetween('created_at', [$inicio, $fim])->get();
        $planos = Plano::whereBetween('created_at', [$inicio, $fim])->get();
        $equipamentos = Equipamento::whereBetween('created_at', [$inicio, $fim])->get();
        $estoque = EstoqueEquipamento::whereBetween('created_at', [$inicio, $fim])->get();
        $clienteEquipamentos = ClienteEquipamento::whereBetween('created_at', [$inicio, $fim])->get();
        $planTemplates = PlanTemplate::whereBetween('created_at', [$inicio, $fim])->get();
        $users = User::whereBetween('created_at', [$inicio, $fim])->get();
        $deletionAudits = DeletionAudit::whereBetween('created_at', [$inicio, $fim])->get();
        $alertas = Plano::with('cliente')
            ->where('estado', 'Ativo')
            ->get()
            ->filter(function($plano) {
                if (!$plano->data_ativacao || !$plano->ciclo) return false;
                $dataTermino = \Carbon\Carbon::parse($plano->data_ativacao)->addDays($plano->ciclo - 1)->startOfDay();
                $diasRestantes = \Carbon\Carbon::today()->diffInDays($dataTermino, false);
                return $diasRestantes >= 0 && $diasRestantes <= 7;
            });
        $fileName = 'relatorio_geral_mensal_' . $inicio->format('Y_m') . '.xlsx';
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
            \Log::error('Falha ao gerar/enviar relatório geral mensal: ' . $e->getMessage(), ['exception' => $e]);
            $this->error('Erro ao gerar relatório geral mensal: ' . $e->getMessage());
            return 1;
        }
        return 0;
    }
}
