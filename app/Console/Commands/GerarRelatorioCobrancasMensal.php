<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cobranca;
use App\Exports\CobrancasExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class GerarRelatorioCobrancasMensal extends Command
{
    protected $signature = 'relatorio:cobrancas-mensal';
    protected $description = 'Gera e envia o relatório mensal de cobranças';

    public function handle()
    {
        $inicio = Carbon::now()->startOfMonth();
        $fim = Carbon::now()->endOfMonth();
        $cobrancas = Cobranca::whereBetween('created_at', [$inicio, $fim])->get();
        if ($cobrancas->isEmpty()) {
            $this->info('Nenhuma cobrança registrada neste mês.');
            return 0;
        }
        $fileName = 'relatorio_cobrancas_mensal_' . $inicio->format('Y_m') . '.xlsx';
        $filePath = 'relatorios/' . $fileName;
        Excel::store(new CobrancasExport($cobrancas), $filePath);
        $this->info('Relatório mensal gerado: ' . $filePath);
        $email = config('mail.from.address');
        if ($email) {
            Mail::raw('Segue em anexo o relatório mensal de cobranças.', function ($message) use ($email, $filePath, $fileName) {
                $message->to($email)
                        ->subject('Relatório Mensal de Cobranças')
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
