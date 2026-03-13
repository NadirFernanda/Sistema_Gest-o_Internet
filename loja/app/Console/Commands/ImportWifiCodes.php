<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WifiCode;

class ImportWifiCodes extends Command
{
    protected $signature = 'wifi-codes:import {file} {--plan= : ID do plano (diario, semanal, mensal)}';
    protected $description = 'Importar códigos WiFi em lote a partir de um arquivo texto (um código por linha)';

    private const VALID_PLANS = ['diario', 'semanal', 'mensal'];

    public function handle()
    {
        $file   = $this->argument('file');
        $planId = $this->option('plan');

        if (!file_exists($file)) {
            $this->error('Arquivo não encontrado: ' . $file);
            return 1;
        }

        if ($planId && !in_array($planId, self::VALID_PLANS, true)) {
            $this->error('Plano inválido. Use: ' . implode(', ', self::VALID_PLANS));
            return 1;
        }

        if (!$planId) {
            $planId = $this->choice('Seleccione o plano para estes códigos:', self::VALID_PLANS);
        }

        $lines    = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $imported = 0;
        foreach ($lines as $line) {
            $code = trim($line);
            if ($code && !WifiCode::where('code', $code)->exists()) {
                WifiCode::create([
                    'code'    => $code,
                    'plan_id' => $planId,
                    'status'  => WifiCode::STATUS_AVAILABLE,
                ]);
                $imported++;
            }
        }
        $this->info("Importados: $imported códigos WiFi para o plano '{$planId}'.");
        return 0;
    }
}
