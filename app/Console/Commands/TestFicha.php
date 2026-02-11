<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use App\Http\Controllers\ClienteController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TestFicha extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:ficha {cliente_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate ficha PDF for a cliente, send email and save a local copy for verification.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->argument('cliente_id');

        $cliente = Cliente::find($id);
        if (! $cliente) {
            $this->error("Cliente {$id} not found.");
            return 1;
        }

        try {
            $this->info("Generating PDF for cliente {$id}...");

            $pdfResult = app()->call([ClienteController::class, 'generateFichaPdfBytes'], ['cliente' => $cliente]);

            // Normalize pdf bytes into a string
            $pdfData = null;
            if (is_string($pdfResult)) {
                $pdfData = $pdfResult;
            } elseif (is_object($pdfResult) && method_exists($pdfResult, 'output')) {
                $pdfData = $pdfResult->output();
            } elseif (is_object($pdfResult) && method_exists($pdfResult, 'getContent')) {
                $pdfData = $pdfResult->getContent();
            } else {
                $pdfData = (string) $pdfResult;
            }

            $path = 'tests/ficha_' . $cliente->id . '.pdf';
            Storage::put($path, $pdfData);
            $this->info('Saved PDF to ' . storage_path('app/' . $path));

            $this->info('Attempting to send email to ' . ($cliente->email ?? 'no-email'));
            app()->call([ClienteController::class, 'sendFichaEmail'], ['cliente' => $cliente, 'pdfBytes' => $pdfData]);
            $this->info('Email send attempted (check queue/logs for details).');

            Log::info("test:ficha completed for cliente {$id}, saved {$path} and attempted send.");
            return 0;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('test:ficha error: ' . $e->getMessage(), ['exception' => $e]);
            return 2;
        }
    }
}
