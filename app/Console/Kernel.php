<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\CorrigirPlanosSemCliente::class,
        \App\Console\Commands\DispararAlertasVencimento::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Dispara alertas de vencimento todos os dias às 8h
        $schedule->command('alertas:disparar')->dailyAt('08:00');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
