<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\CorrigirPlanosSemCliente::class,
        \App\Console\Commands\DispararAlertasVencimento::class,
        \App\Console\Commands\GerarRelatorioGeralDiario::class,
        \App\Console\Commands\GerarRelatorioGeralSemanal::class,
        \App\Console\Commands\GerarRelatorioGeralMensal::class,
        \App\Console\Commands\GerarFichaPdf::class,
        \App\Console\Commands\TestDomPdf::class,
        \App\Console\Commands\TestFicha::class,
        \App\Console\Commands\MigratePlansToTemplates::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Dispara alertas de vencimento duas vezes ao dia (ex.: 09:00 e 18:00)
        $schedule->command('alertas:disparar')->twiceDaily(9, 18);
        // Gera relatório geral diário do sistema todos os dias às 7h (últimas 24h)
        $schedule->command('relatorio:geral-diario --last-hours=24')->dailyAt('07:00');
        // Gera relatório geral semanal do sistema toda segunda-feira às 7h10
        $schedule->command('relatorio:geral-semanal')->weeklyOn(1, '07:10');
        // Gera relatório geral mensal do sistema todo dia 1 às 7h20
        $schedule->command('relatorio:geral-mensal')->monthlyOn(1, '07:20');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
