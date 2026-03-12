<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteStat;

class SiteStatsSeeder extends Seeder
{
    public function run(): void
    {
        SiteStat::truncate();

        $stats = [
            // count_to deliberadamente NULL para "Clientes activos": o valor real vem do SG em tempo real.
            // Mostrar um número fixo aqui seria enganoso — a view usa a API do SG ou exibe “—”.
            ['ordem' => 1, 'valor' => '—',     'legenda' => 'Clientes activos',  'count_to' => null,  'count_decimals' => 0, 'count_suffix' => null],
            ['ordem' => 2, 'valor' => '99.8%', 'legenda' => 'Uptime garantido',  'count_to' => 99.8,  'count_decimals' => 1, 'count_suffix' => '%'],
            ['ordem' => 3, 'valor' => '24–48h','legenda' => 'Instalação rápida', 'count_to' => null,  'count_decimals' => 0, 'count_suffix' => null],
            ['ordem' => 4, 'valor' => '24/7',   'legenda' => 'Suporte técnico',   'count_to' => null,  'count_decimals' => 0, 'count_suffix' => null],
        ];

        foreach ($stats as $stat) {
            SiteStat::create($stat);
        }
    }
}
