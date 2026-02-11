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
            // Prepare logo as data URI (embed) to avoid remote fetch issues
            $logoData = null;
            $logoPath = public_path('img/logo2.jpeg');
            if (file_exists($logoPath)) {
                $type = mime_content_type($logoPath) ?: 'image/jpeg';
                $data = base64_encode(file_get_contents($logoPath));
                $logoData = "data:{$type};base64,{$data}";
            }

            // Render the view to HTML first (helpful for debugging)
            $html = view('pdf.ficha_cliente', compact('cliente', 'logoData'))->render();

            // Ensure storage dir exists
            $dir = storage_path('app/fichas');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Save HTML for inspection
            file_put_contents($dir . DIRECTORY_SEPARATOR . "ficha_cliente_{$id}.html", $html);


            $path = $dir . DIRECTORY_SEPARATOR . "ficha_cliente_{$id}.pdf";

            // Prefer mPDF if available (more robust on some hosts)
            if (class_exists('\\Mpdf\\Mpdf')) {
                try {
                    $mpdf = new \\Mpdf\\Mpdf(['tempDir' => sys_get_temp_dir()]);
                    $mpdf->WriteHTML($html);
                    $mpdf->Output($path, \\Mpdf\\Output\\Destination::FILE);
                } catch (\Exception $e) {
                    \Log::warning('mPDF generation failed in CLI; falling back to DOMPDF', ['error' => $e->getMessage()]);
                }
            }

            // If file not created by mPDF, try DOMPDF
            if (!file_exists($path) || filesize($path) < 1024) {
                try {
                    $pdf = \PDF::loadHTML($html);
                    $pdf->setPaper('a4', 'portrait');
                    if (method_exists($pdf, 'setOptions')) {
                        $pdf->setOptions(['isRemoteEnabled' => true, 'enable_php' => false]);
                    }
                    file_put_contents($path, $pdf->output());
                } catch (\Exception $e) {
                    \Log::warning('DOMPDF generation failed in CLI', ['error' => $e->getMessage()]);
                }
            }

            $this->info("Ficha salva em: {$path}");
            $this->info("HTML salvo em: " . ($dir . DIRECTORY_SEPARATOR . "ficha_cliente_{$id}.html"));
            // Also generate a minimal debug PDF (plain text) to check DOMPDF rendering
            try {
                $plainHtml = '<html><body><h1>Ficha de Cliente (Debug)</h1><p>ID: ' . e($cliente->id) . '</p><p>Nome: ' . e($cliente->nome) . '</p></body></html>';
                $plainPdf = \PDF::loadHTML($plainHtml);
                $plainPdf->setPaper('a4', 'portrait');
                if (method_exists($plainPdf, 'setOptions')) {
                    $plainPdf->setOptions(['isRemoteEnabled' => true, 'enable_php' => false]);
                }
                $plainPath = $dir . DIRECTORY_SEPARATOR . "ficha_cliente_{$id}_debug.pdf";
                file_put_contents($plainPath, $plainPdf->output());
                file_put_contents($dir . DIRECTORY_SEPARATOR . "ficha_cliente_{$id}_debug.html", $plainHtml);
                $this->info("Debug PDF salvo em: {$plainPath}");
            } catch (\Exception $e) {
                $this->error('Erro ao gerar PDF de debug: ' . $e->getMessage());
            }

            // If DOMPDF produced an empty/invalid PDF, try mPDF as a fallback
            try {
                $stat = @filesize($path) ?: 0;
                if ($stat < 1024 || strpos(file_get_contents($path), '/Type /Page') === false) {
                    if (class_exists('\Mpdf\Mpdf')) {
                        $this->info('DOMPDF output looks invalid or empty — trying mPDF fallback...');
                        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir()]);
                        $mpdf->WriteHTML($html);
                        $mpdf->Output($path, \Mpdf\Output\Destination::FILE);
                        $this->info('mPDF fallback saved to: ' . $path);
                        // Also replace debug PDF with mPDF version
                        $mpdf->WriteHTML($plainHtml);
                        $mpdf->Output($dir . DIRECTORY_SEPARATOR . "ficha_cliente_{$id}_debug.pdf", \Mpdf\Output\Destination::FILE);
                        $this->info('mPDF debug PDF saved.');
                    } else {
                        $this->warn('mPDF not installed; cannot attempt mPDF fallback.');
                    }
                }
            } catch (\Exception $e) {
                $this->error('Erro no fallback mPDF: ' . $e->getMessage());
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Erro ao gerar PDF: ' . $e->getMessage());
            return 1;
        }
    }
}
