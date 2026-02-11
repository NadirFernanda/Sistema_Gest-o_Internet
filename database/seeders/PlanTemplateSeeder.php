<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlanTemplate;

class PlanTemplateSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            [
                'name' => 'Plano Básico',
                'description' => 'Plano residencial 10Mbps - básico',
                'preco' => 5000.00,
                'ciclo' => 30,
                'estado' => 'Ativo',
            ],
            [
                'name' => 'Plano Intermediário',
                'description' => 'Plano residencial 30Mbps - intermediário',
                'preco' => 9000.00,
                'ciclo' => 30,
                'estado' => 'Ativo',
            ],
            [
                'name' => 'Plano Empresarial',
                'description' => 'Plano empresarial 100Mbps',
                'preco' => 25000.00,
                'ciclo' => 30,
                'estado' => 'Ativo',
            ],
        ];

        foreach ($templates as $t) {
            PlanTemplate::updateOrCreate(['name' => $t['name']], $t);
        }
    }
}
