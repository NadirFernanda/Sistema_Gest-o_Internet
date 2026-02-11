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
            // Render the view to HTML first (helpful for debugging)
            $html = view('pdf.ficha_cliente', compact('cliente'))->render();

            // Ensure storage dir exists
            $dir = storage_path('app/fichas');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Save HTML for inspection
            file_put_contents($dir . DIRECTORY_SEPARATOR . "ficha_cliente_{$id}.html", $html);

            // Configure DOMPDF options: enable remote assets and set paper size
            $pdf = \PDF::loadHTML($html);
            $pdf->setPaper('a4', 'portrait');
            if (method_exists($pdf, 'setOptions')) {
                $pdf->setOptions(['isRemoteEnabled' => true, 'enable_php' => false]);
            }

            $output = $pdf->output();

            $path = $dir . DIRECTORY_SEPARATOR . "ficha_cliente_{$id}.pdf";
            file_put_contents($path, $output);

            $this->info("Ficha salva em: {$path}");
            $this->info("HTML salvo em: " . ($dir . DIRECTORY_SEPARATOR . "ficha_cliente_{$id}.html"));
            return 0;
        } catch (\Exception $e) {
            $this->error('Erro ao gerar PDF: ' . $e->getMessage());
            return 1;
        }
    }
}
