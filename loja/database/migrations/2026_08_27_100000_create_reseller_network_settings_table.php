<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reseller_network_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 80)->unique();
            $table->string('label');
            $table->string('description')->nullable();
            $table->decimal('value', 8, 2)->default(0);
            $table->string('unit', 20)->default('%');
            $table->timestamps();
        });

        DB::table('reseller_network_settings')->insert([
            [
                'key'         => 'mode_own_discount_percent',
                'label'       => 'Desconto — Modo Próprio (internet própria)',
                'description' => 'Percentagem de desconto sobre o preço público para revendedores com internet própria (Modo 1).',
                'value'       => 70.00,
                'unit'        => '%',
                'created_at'  => now(), 'updated_at' => now(),
            ],
            [
                'key'         => 'mode_angolawifi_discount_percent',
                'label'       => 'Desconto — Modo AngolaWiFi (internet AngolaWiFi)',
                'description' => 'Percentagem de desconto sobre o preço público para revendedores que usam a internet AngolaWiFi (Modo 2).',
                'value'       => 30.00,
                'unit'        => '%',
                'created_at'  => now(), 'updated_at' => now(),
            ],
            [
                'key'         => 'bonus_install_percent',
                'label'       => 'Bónus de instalação (% sobre a taxa de instalação)',
                'description' => 'Percentagem da taxa de instalação convertida em vouchers de bónus para o revendedor.',
                'value'       => 50.00,
                'unit'        => '%',
                'created_at'  => now(), 'updated_at' => now(),
            ],
            [
                'key'         => 'monthly_target_percent',
                'label'       => 'Meta mensal (% sobre a taxa de instalação)',
                'description' => 'Percentagem da taxa de instalação usada como meta de vendas mensais para revendedores Modo 1.',
                'value'       => 50.00,
                'unit'        => '%',
                'created_at'  => now(), 'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('reseller_network_settings');
    }
};
