<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GerarFichaPdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ficha:gerar {cliente : ID do cliente}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera o PDF da ficha de um cliente e salva em storage/app/fichas';

    public function handle()
    {
        $id = $this->argument('cliente');

        $this->info("Gerando ficha para o cliente ID={$id}...");

        $cliente = \App\Models\Cliente::with(['planos', 'equipamentos', 'cobrancas'])->find($id);

        if (! $cliente) {
            $this->error("Cliente ID={$id} não encontrado.");
            return 1;
        }

        try {
            $pdf = \PDF::loadView('pdf.ficha_cliente', compact('cliente'));

            $output = $pdf->output();

            $dir = storage_path('app/fichas');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $path = $dir . DIRECTORY_SEPARATOR . "ficha_cliente_{$id}.pdf";
            file_put_contents($path, $output);

            $this->info("Ficha salva em: {$path}");
            return 0;
        } catch (\Exception $e) {
            $this->error('Erro ao gerar PDF: ' . $e->getMessage());
            return 1;
        }
    }
}
