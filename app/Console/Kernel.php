<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\CorrigirPlanosSemCliente::class,
        \App\Console\Commands\DispararAlertasVencimento::class,
        \App\Console\Commands\GerarRelatorioCobrancasDiario::class,
        \App\Console\Commands\GerarRelatorioCobrancasSemanal::class,
        \App\Console\Commands\GerarRelatorioCobrancasMensal::class,
        \App\Console\Commands\GerarFichaPdf::class,
        \App\Console\Commands\TestDomPdf::class,
        \App\Console\Commands\TestFicha::class,
        \App\Console\Commands\MigratePlansToTemplates::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Dispara alertas de vencimento duas vezes ao dia (ex.: 09:00 e 18:00)
        $schedule->command('alertas:disparar')->twiceDaily(9, 18);
        // Gera relatório diário de cobranças todos os dias às 7h
        $schedule->command('relatorio:cobrancas-diario')->dailyAt('07:00');
        // Gera relatório semanal de cobranças todo domingo às 7h10
        $schedule->command('relatorio:cobrancas-semanal')->weeklyOn(0, '07:10');
        // Gera relatório mensal de cobranças todo dia 1 às 7h20
        $schedule->command('relatorio:cobrancas-mensal')->monthlyOn(1, '07:20');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
