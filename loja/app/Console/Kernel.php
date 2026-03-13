<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\ImportWifiCodes::class,
        \App\Console\Commands\AlertResellers::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Run daily at 08:00 — sends maintenance/target alerts on the 1st of each relevant month
        $schedule->command('resellers:alert')->dailyAt('08:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
