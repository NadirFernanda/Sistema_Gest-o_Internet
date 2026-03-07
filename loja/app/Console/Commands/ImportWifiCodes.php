<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WifiCode;

class ImportWifiCodes extends Command
{
    protected $signature = 'wifi-codes:import {file}';
    protected $description = 'Importar códigos WiFi em lote a partir de um arquivo texto (um código por linha)';

    public function handle()
    {
        $file = $this->argument('file');
        if (!file_exists($file)) {
            $this->error('Arquivo não encontrado: ' . $file);
            return 1;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $imported = 0;
        foreach ($lines as $line) {
            $code = trim($line);
            if ($code && !WifiCode::where('code', $code)->exists()) {
                WifiCode::create(['code' => $code, 'status' => WifiCode::STATUS_AVAILABLE]);
                $imported++;
            }
        }
        $this->info("Importados: $imported códigos WiFi.");
        return 0;
    }
}
