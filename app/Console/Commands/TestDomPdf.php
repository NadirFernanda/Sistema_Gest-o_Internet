<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Barryvdh\DomPDF\Facade\Pdf;

class TestDomPdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:dompdf {--html : Use a basic HTML template}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a small test PDF using the DomPDF facade and saves to storage/app/test-dompdf.pdf';

    public function handle()
    {
        $this->info('Running DomPDF test...');

        try {
            if (! class_exists(Pdf::class)) {
                $this->error('Pdf facade class not found. Is barryvdh/laravel-dompdf installed?');
                return 1;
            }

            $html = '<html><body><h1>DomPDF Test</h1><p>Generated at ' . now() . '</p></body></html>';
            $pdf = Pdf::loadHTML($html);
            $output = $pdf->output();
            $path = storage_path('app/test-dompdf.pdf');
            file_put_contents($path, $output);

            $this->info('PDF written to: ' . $path);
            return 0;
        } catch (\Throwable $e) {
            $this->error('Exception: ' . $e->getMessage());
            return 1;
        }
    }
}
