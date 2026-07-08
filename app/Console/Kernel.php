<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\CorrigirPlanosSemCliente::class,
        \App\Console\Commands\DispararAlertasVencimento::class,
        \App\Console\Commands\GerarFichaPdf::class,
        \App\Console\Commands\RelatorioGeralCommand::class,
        \App\Console\Commands\TestDomPdf::class,
        \App\Console\Commands\TestFicha::class,
        \App\Console\Commands\MigratePlansToTemplates::class,
        \App\Console\Commands\ResetAdminPassword::class,
        \App\Console\Commands\AuditBackfillCommand::class,
        \App\Console\Commands\EnviarNotificacaoDevolucao::class,
        \App\Console\Commands\MikroTikSyncPlans::class,
        \App\Console\Commands\MikroTikExpirePlans::class,
        \App\Console\Commands\MikroTikCheckOnlineStatus::class,
        \App\Console\Commands\MikroTikBackfillDisconnectReasons::class,
        \App\Console\Commands\MikroTikSampleBandwidth::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        Log::info('Console Kernel::schedule() invoked to register scheduled tasks');
        // Dispara alertas de vencimento duas vezes ao dia (13:00 e 18:00)
        $schedule->command('alertas:disparar')->twiceDaily(13, 18);
        // Lembrete diário de cobrança para planos Suspensos (pagamento em atraso)
        $schedule->command('alertas:disparar --apenas-suspensos')
            ->dailyAt('10:00')
            ->timezone(config('app.timezone'))
            ->withoutOverlapping()
            ->runInBackground();
        // Envia avisos de devolução diariamente para cobranças vencidas há mais de 30 dias
        $schedule->command('notificacao:devolucao')
            ->dailyAt('09:00')
            ->timezone(config('app.timezone'))
            ->withoutOverlapping()
            ->runInBackground();
        // Gera relatórios gerais automaticamente (tornar robusto: timezone, sem overlap, em background)
        $schedule->command('relatorio:geral --period=daily')
            ->dailyAt('00:05')
            ->timezone(config('app.timezone'))
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('relatorio:geral --period=weekly')
            ->weeklyOn(1, '00:10')
            ->timezone(config('app.timezone'))
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('relatorio:geral --period=monthly')
            ->monthlyOn(1, '00:15')
            ->timezone(config('app.timezone'))
            ->withoutOverlapping()
            ->runInBackground();
        // MikroTik: sincroniza planos activos a cada 5 minutos
        $schedule->command('mikrotik:sync-plans')
            ->everyFiveMinutes()
            ->timezone(config('app.timezone'))
            ->withoutOverlapping()
            ->runInBackground();

        // MikroTik: amostra largura de banda a cada minuto
        $schedule->command('mikrotik:sample-bandwidth')
            ->everyMinute()
            ->timezone(config('app.timezone'))
            ->withoutOverlapping()
            ->runInBackground();

        // MikroTik: verifica status online dos clientes a cada 5 minutos
        $schedule->command('mikrotik:check-online-status')
            ->everyFiveMinutes()
            ->timezone(config('app.timezone'))
            ->withoutOverlapping()
            ->runInBackground();

        // MikroTik: suspende utilizadores com plano vencido à meia-noite (00:01 para não colidir com relatórios)
        $schedule->command('mikrotik:expire-plans')
            ->dailyAt('00:01')
            ->timezone(config('app.timezone'))
            ->withoutOverlapping()
            ->runInBackground();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
