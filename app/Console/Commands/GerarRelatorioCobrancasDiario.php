<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cobranca;
use App\Exports\RelatorioMultiAbaExport;
use App\Models\Cliente;
use App\Models\Plano;
use App\Models\Equipamento;
use App\Models\EstoqueEquipamento;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class GerarRelatorioCobrancasDiario extends Command
{
    protected $signature = 'relatorio:cobrancas-diario';
    protected $description = 'Gera e envia o relatório diário de cobranças';

    public function handle()
    {
        $hoje = Carbon::today();
        $cobrancas = Cobranca::whereDate('created_at', $hoje)->get();
        $clientes = Cliente::all();
        $planos = Plano::all();
        $equipamentos = Equipamento::all();
        $estoque = EstoqueEquipamento::all();
        // Alertas: planos a vencer nos próximos 7 dias
        $alertas = Plano::with('cliente')
            ->where('estado', 'Ativo')
            ->get()
            ->filter(function($plano) {
                if (!$plano->data_ativacao || !$plano->ciclo) return false;
                $dataTermino = \Carbon\Carbon::parse($plano->data_ativacao)->addDays($plano->ciclo - 1)->startOfDay();
                $diasRestantes = \Carbon\Carbon::today()->diffInDays($dataTermino, false);
                return $diasRestantes >= 0 && $diasRestantes <= 7;
            });
        $fileName = 'relatorio_geral_diario_' . $hoje->format('Y_m_d') . '.xlsx';
        $filePath = 'relatorios/' . $fileName;
        Excel::store(new RelatorioMultiAbaExport($cobrancas, $clientes, $planos, $equipamentos, $alertas), $filePath);
        $this->info('Relatório diário multi-aba gerado: ' . $filePath);
        // Opcional: enviar por e-mail
        $email = config('mail.from.address');
        if ($email) {
            Mail::raw('Segue em anexo o relatório diário de cobranças.', function ($message) use ($email, $filePath, $fileName) {
                $message->to($email)
                        ->subject('Relatório Diário de Cobranças')
                        ->attach(storage_path('app/' . $filePath), [
                            'as' => $fileName,
                            'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ]);
            });
            $this->info('Relatório enviado para: ' . $email);
        }
        return 0;
    }
}
