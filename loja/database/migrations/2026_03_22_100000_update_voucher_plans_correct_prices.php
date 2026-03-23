<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Correct the voucher plan prices, speed and validity labels
        // to match config/store_plans.php (source of truth).
        $updates = [
            'diario'  => ['price_public_aoa' => 200, 'price_reseller_aoa' => 120, 'validity_label' => '24 horas', 'speed_label' => 'Até 10 Mbps'],
            'semanal' => ['price_public_aoa' => 500, 'price_reseller_aoa' => 300, 'validity_label' => '7 dias',   'speed_label' => 'Até 10 Mbps'],
            'mensal'  => ['price_public_aoa' => 1000,'price_reseller_aoa' => 600, 'validity_label' => '30 dias',  'speed_label' => 'Até 10 Mbps'],
        ];

        foreach ($updates as $slug => $values) {
            DB::table('voucher_plans')->where('slug', $slug)->update($values);
        }
    }

    public function down(): void
    {
        $original = [
            'diario'  => ['price_public_aoa' => 500,  'price_reseller_aoa' => 300,  'validity_label' => '1 dia',   'speed_label' => '5 Mbps'],
            'semanal' => ['price_public_aoa' => 2500, 'price_reseller_aoa' => 1500, 'validity_label' => '7 dias',  'speed_label' => '5 Mbps'],
            'mensal'  => ['price_public_aoa' => 8000, 'price_reseller_aoa' => 5000, 'validity_label' => '30 dias', 'speed_label' => '10 Mbps'],
        ];

        foreach ($original as $slug => $values) {
            DB::table('voucher_plans')->where('slug', $slug)->update($values);
        }
    }
};
