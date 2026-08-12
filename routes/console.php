<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Ciclo de vencimento (email + WhatsApp, 4 estágios) — uma vez por dia.
// withoutOverlapping evita que uma execução lenta (ex.: muitas pausas
// anti-spam do WhatsApp) se sobreponha à do dia seguinte.
Schedule::command('alertas:disparar')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->onOneServer();
