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
                'validity_label'     => '1 dia',
                'validity_minutes'   => 1440,
                'speed_label'        => '5 Mbps',
                'price_public_aoa'   => 500,
                'price_reseller_aoa' => 300,
                'active'             => true,
                'sort_order'         => 1,
            ],
            [
                'slug'               => 'semanal',
                'name'               => 'Plano Semanal',
                'validity_label'     => '7 dias',
                'validity_minutes'   => 10080,
                'speed_label'        => '5 Mbps',
                'price_public_aoa'   => 2500,
                'price_reseller_aoa' => 1500,
                'active'             => true,
                'sort_order'         => 2,
            ],
            [
                'slug'               => 'mensal',
                'name'               => 'Plano Mensal',
                'validity_label'     => '30 dias',
                'validity_minutes'   => 43200,
                'speed_label'        => '10 Mbps',
                'price_public_aoa'   => 8000,
                'price_reseller_aoa' => 5000,
                'active'             => true,
                'sort_order'         => 3,
            ],
        ];

        foreach ($plans as $plan) {
            VoucherPlan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
