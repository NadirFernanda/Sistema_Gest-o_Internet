<?php

namespace Database\Seeders;

use App\Models\VoucherPlan;
use Illuminate\Database\Seeder;

class VoucherPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug'               => 'diario',
                'name'               => 'Plano Diário',
                'validity_label'     => '24 horas',
                'validity_minutes'   => 1440,
                'speed_label'        => 'Velocidade até 10 Mbps',
                'price_public_aoa'   => 200,
                'price_reseller_aoa' => 120,
                'active'             => true,
                'sort_order'         => 1,
            ],
            [
                'slug'               => 'semanal',
                'name'               => 'Plano Semanal',
                'validity_label'     => '7 dias',
                'validity_minutes'   => 10080,
                'speed_label'        => 'Velocidade até 10 Mbps',
                'price_public_aoa'   => 500,
                'price_reseller_aoa' => 300,
                'active'             => true,
                'sort_order'         => 2,
            ],
            [
                'slug'               => 'mensal',
                'name'               => 'Plano Mensal',
                'validity_label'     => '30 dias',
                'validity_minutes'   => 43200,
                'speed_label'        => 'Velocidade até 10 Mbps',
                'price_public_aoa'   => 1000,
                'price_reseller_aoa' => 600,
                'active'             => true,
                'sort_order'         => 3,
            ],
        ];

        foreach ($plans as $plan) {
            VoucherPlan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
