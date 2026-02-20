<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Plano;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DispararAlertasVencimento extends Command
{
    protected $signature = 'alertas:disparar {--dias=5 : Dias para vencimento}';
    protected $description = 'Dispara alertas de vencimento por e-mail e WhatsApp para planos próximos do vencimento';

    public function handle()
    {
        $dias = (int) $this->option('dias');
        $hoje = Carbon::today(); // apenas a data, sem hora
        // Be tolerant to casing/spacing of 'estado' and accept empty/null as active
        $planosRaw = Plano::with('cliente')
            ->where(function($q){
                $q->whereRaw("LOWER(TRIM(COALESCE(estado, ''))) LIKE ?", ['%ativ%'])
                  ->orWhereRaw("LOWER(TRIM(COALESCE(estado, ''))) LIKE ?", ['%activ%'])
                  ->orWhereRaw("COALESCE(estado, '') = ''");
            })
            ->get();
        $planos = $planosRaw->filter(function($plano) use ($dias, $hoje) {
            // Determine data de término: preferir proxima_renovacao quando presente
            try {
                if (!empty($plano->proxima_renovacao)) {
                    $dataTermino = Carbon::parse($plano->proxima_renovacao)->startOfDay();
                } elseif (!empty($plano->data_ativacao) && $plano->ciclo) {
                    $cicloInt = intval(preg_replace('/[^0-9]/', '', (string)$plano->ciclo));
                    if ($cicloInt <= 0) {
                        $cicloInt = (int)$plano->ciclo;
                    }
                    $dataTermino = Carbon::parse($plano->data_ativacao)->addDays($cicloInt - 1)->startOfDay();
                } else {
                    $this->info('Ignorado: plano sem data_ativacao, ciclo ou proxima_renovacao. ID: ' . $plano->id);
                    return false;
                }
                $diasRestantes = $hoje->diffInDays($dataTermino, false);
            } catch (\Exception $e) {
                $this->info('Ignorado: erro ao parsear datas do plano ID: ' . $plano->id . ' - ' . $e->getMessage());
                return false;
            }
            $this->info('Plano: ' . ($plano->cliente ? $plano->cliente->nome : '-') . ' | Ativação: ' . $plano->data_ativacao . ' | Ciclo: ' . $plano->ciclo . ' | Término: ' . $dataTermino->toDateString() . ' | DiasRestantes: ' . $diasRestantes . ' | Estado: ' . $plano->estado);
            return $diasRestantes >= 0 && $diasRestantes <= $dias;
        });
        if ($planos->isEmpty()) {
            $this->info('Nenhum plano a vencer nos próximos ' . $dias . ' dias.');
            Log::info('alertas:disparar - nenhum plano encontrado', ['dias' => $dias]);
            return 0;
        }

        $start = microtime(true);
        $sent = 0;
        $failed = 0;
        Log::info('alertas:disparar - iniciando dispatch', ['dias' => $dias, 'candidates' => $planos->count()]);
        // Ensure dispatch log exists (best-effort)
        try {
            $logDir = storage_path('logs');
            if (!is_dir($logDir)) { mkdir($logDir, 0755, true); }
            $startLine = '[' . now()->toDateTimeString() . '] iniciar dispatch dias=' . $dias . " candidates=" . $planos->count() . "\n";
            @file_put_contents($logDir . '/alerts-dispatch.log', $startLine, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $_) {
            // ignore any filesystem problems here
        }

        foreach ($planos as $plano) {
            try {
                if (!empty($plano->proxima_renovacao)) {
                    $dataTermino = Carbon::parse($plano->proxima_renovacao)->startOfDay();
                } else {
                    $cicloInt = intval(preg_replace('/[^0-9]/', '', (string)$plano->ciclo));
                    if ($cicloInt <= 0) { $cicloInt = (int)$plano->ciclo; }
                    $dataTermino = Carbon::parse($plano->data_ativacao)->addDays($cicloInt - 1)->startOfDay();
                }
                $diasRestantes = Carbon::today()->diffInDays($dataTermino, false);
            } catch (\Exception $e) {
                $this->info('Falha ao calcular dataTermino para plano ID: ' . $plano->id . ' - ' . $e->getMessage());
                continue;
            }
            if ($plano->cliente) {
                $cliente = $plano->cliente;
                // Send mail (primary channel) and count attempts separately; if one channel fails, continue
                try {
                    $cliente->notify(new \App\Notifications\ClienteVencimentoAlert($cliente, $plano, $diasRestantes));
                    $sent++;
                    $this->info('E-mail enviado para: ' . ($cliente->email ?? '-') . ' (diasRestantes: ' . $diasRestantes . ')');
                    Log::info('alertas:disparar - mail enviado', ['plano_id' => $plano->id, 'cliente_id' => $cliente->id ?? null, 'email' => $cliente->email ?? null, 'diasRestantes' => $diasRestantes]);
                } catch (\Throwable $e) {
                    $failed++;
                    $this->error('Falha ao enviar e-mail para plano ID ' . $plano->id . ': ' . $e->getMessage());
                    Log::error('alertas:disparar - falha mail', ['plano_id' => $plano->id, 'err' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                    try { @file_put_contents(storage_path('logs/alerts-dispatch.log'), '[' . now()->toDateTimeString() . '] Falha mail plano ' . $plano->id . ' - ' . $e->getMessage() . "\n", FILE_APPEND | LOCK_EX); } catch (\Throwable $_) {}
                }

                // Attempt WhatsApp separately; if the driver is missing, log a warning and continue
                try {
                    $cliente->notify(new \App\Notifications\ClienteVencimentoWhatsApp($cliente, $plano, $diasRestantes));
                    $sent++;
                    $this->info('WhatsApp enviado para: ' . ($cliente->contato ?? '-') . ' (diasRestantes: ' . $diasRestantes . ')');
                    Log::info('alertas:disparar - whatsapp enviado', ['plano_id' => $plano->id, 'cliente_id' => $cliente->id ?? null, 'contato' => $cliente->contato ?? null, 'diasRestantes' => $diasRestantes]);
                } catch (\Throwable $e) {
                    // Detect unsupported driver error and treat it as non-fatal
                    $msg = $e->getMessage();
                    if ($e instanceof \InvalidArgumentException || stripos($msg, 'Driver [whatsapp]') !== false || (stripos($msg, 'whatsapp') !== false && stripos($msg, 'not supported') !== false)) {
                        $this->warn('Canal WhatsApp não suportado no ambiente - pulando envio WhatsApp para plano ID ' . $plano->id);
                        Log::warning('alertas:disparar - whatsapp driver ausente', ['plano_id' => $plano->id, 'err' => $msg]);
                        try { @file_put_contents(storage_path('logs/alerts-dispatch.log'), '[' . now()->toDateTimeString() . '] Whatsapp ausente plano ' . $plano->id . ' - ' . $msg . "\n", FILE_APPEND | LOCK_EX); } catch (\Throwable $_) {}
                    } else {
                        $failed++;
                        $this->error('Falha ao enviar WhatsApp para plano ID ' . $plano->id . ': ' . $msg);
                        Log::error('alertas:disparar - falha whatsapp', ['plano_id' => $plano->id, 'err' => $msg, 'trace' => $e->getTraceAsString()]);
                        try { @file_put_contents(storage_path('logs/alerts-dispatch.log'), '[' . now()->toDateTimeString() . '] Falha whatsapp plano ' . $plano->id . ' - ' . $msg . "\n", FILE_APPEND | LOCK_EX); } catch (\Throwable $_) {}
                    }
                }
            }
        }
        $duration = round(microtime(true) - $start, 2);
        $this->info("Alertas disparados: sucesso={$sent}, falhas={$failed}, tempo={$duration}s");
        Log::info('alertas:disparar - finalizado', ['sent' => $sent, 'failed' => $failed, 'duration_s' => $duration]);
        try {
            $summaryLine = '[' . now()->toDateTimeString() . "] sent={$sent} failed={$failed} duration_s={$duration}\n";
            file_put_contents(storage_path('logs/alerts-dispatch.log'), $summaryLine, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $_) {}

        return 0;
    }
}
