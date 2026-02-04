<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cobranca;
use App\Exports\CobrancasExport;
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
        if ($cobrancas->isEmpty()) {
            $this->info('Nenhuma cobrança registrada hoje.');
            return 0;
        }
        $fileName = 'relatorio_cobrancas_diario_' . $hoje->format('Y_m_d') . '.xlsx';
        $filePath = 'relatorios/' . $fileName;
        // Salva o arquivo no storage/app/relatorios
        Excel::store(new CobrancasExport($cobrancas), $filePath);
        $this->info('Relatório diário gerado: ' . $filePath);
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
