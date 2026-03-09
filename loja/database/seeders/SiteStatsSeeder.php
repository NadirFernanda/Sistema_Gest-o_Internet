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
            ['ordem' => 1, 'valor' => '5.000+',  'legenda' => 'Clientes activos',   'count_to' => 5000,  'count_decimals' => 0, 'count_suffix' => '+'],
            ['ordem' => 2, 'valor' => '99.8%',   'legenda' => 'Uptime garantido',   'count_to' => 99.8,  'count_decimals' => 1, 'count_suffix' => '%'],
            ['ordem' => 3, 'valor' => '24–48h',  'legenda' => 'Instalação rápida',  'count_to' => null,  'count_decimals' => 0, 'count_suffix' => null],
            ['ordem' => 4, 'valor' => '24/7',    'legenda' => 'Suporte técnico',    'count_to' => null,  'count_decimals' => 0, 'count_suffix' => null],
        ];

        foreach ($stats as $stat) {
            SiteStat::create($stat);
        }
    }
}
