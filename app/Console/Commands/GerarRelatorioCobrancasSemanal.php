<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cobranca;
use App\Exports\CobrancasExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class GerarRelatorioCobrancasSemanal extends Command
{
    protected $signature = 'relatorio:cobrancas-semanal';
    protected $description = 'Gera e envia o relatório semanal de cobranças';

    public function handle()
    {
        $inicio = Carbon::now()->startOfWeek();
        $fim = Carbon::now()->endOfWeek();
        $cobrancas = Cobranca::whereBetween('created_at', [$inicio, $fim])->get();
        if ($cobrancas->isEmpty()) {
            $this->info('Nenhuma cobrança registrada nesta semana.');
            return 0;
        }
        $fileName = 'relatorio_cobrancas_semanal_' . $inicio->format('Y_m_d') . '_a_' . $fim->format('Y_m_d') . '.xlsx';
        $filePath = 'relatorios/' . $fileName;
        Excel::store(new CobrancasExport($cobrancas), $filePath);
        $this->info('Relatório semanal gerado: ' . $filePath);
        $email = config('mail.from.address');
        if ($email) {
            Mail::raw('Segue em anexo o relatório semanal de cobranças.', function ($message) use ($email, $filePath, $fileName) {
                $message->to($email)
                        ->subject('Relatório Semanal de Cobranças')
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
