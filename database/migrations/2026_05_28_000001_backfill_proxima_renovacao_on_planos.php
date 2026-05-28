<?php

use App\Models\Plano;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Plano::whereNull('proxima_renovacao')
            ->whereNotNull('data_ativacao')
            ->whereNotNull('ciclo')
            ->where('ciclo', '>', 0)
            ->each(function (Plano $plano) {
                $plano->proxima_renovacao = Carbon::parse($plano->data_ativacao)
                    ->addDays((int) $plano->ciclo)
                    ->toDateString();
                $plano->saveQuietly();
            });
    }

    public function down(): void
    {
        // Irreversível: não é possível saber quais datas foram calculadas vs definidas manualmente
    }
};
