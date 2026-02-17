<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\CorrigirPlanosSemCliente::class,
        \App\Console\Commands\DispararAlertasVencimento::class,
        \App\Console\Commands\GerarFichaPdf::class,
        \App\Console\Commands\TestDomPdf::class,
        \App\Console\Commands\TestFicha::class,
        \App\Console\Commands\MigratePlansToTemplates::class,
        \App\Console\Commands\ResetAdminPassword::class,
        \App\Console\Commands\AuditBackfillCommand::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Dispara alertas de vencimento duas vezes ao dia (ex.: 09:00 e 18:00)
        $schedule->command('alertas:disparar')->twiceDaily(9, 18);
        // Scheduled tasks for general reports have been removed (legacy audit reports)
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
