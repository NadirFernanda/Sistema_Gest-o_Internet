<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona campos de lógica de negócio à tabela reseller_applications:
     *
     *  reseller_mode     — 'own' (Modo 1, internet própria) | 'angolawifi' (Modo 2)
     *  installation_fee_aoa — Taxa de instalação paga pelo agente (Kz)
     *  bonus_vouchers_aoa   — 50% da taxa de instalação convertido em vouchers no início
     *  monthly_target_aoa   — Meta mínima de compra mensal (50% da instalação - Modo 1)
     *  maintenance_paid_year — Ano em que a taxa de manutenção foi paga pela última vez
     *  maintenance_status    — 'ok' | 'pending' | 'overdue'
     *  notes                 — Notas internas do admin
     */
    public function up(): void
    {
        Schema::table('reseller_applications', function (Blueprint $table) {
            // Modo do revendedor: 'own' = internet própria (Modo 1), 'angolawifi' = Modo 2
            $table->string('reseller_mode', 32)->nullable()->after('internet_type');

            // Taxa de instalação
            $table->unsignedBigInteger('installation_fee_aoa')->default(0)->after('reseller_mode');

            // Vouchers de bónus de início (50% da instalação) — gerados automaticamente
            $table->unsignedBigInteger('bonus_vouchers_aoa')->default(0)->after('installation_fee_aoa');

            // Meta mensal mínima (50% da instalação para Modo 1)
            $table->unsignedBigInteger('monthly_target_aoa')->default(0)->after('bonus_vouchers_aoa');

            // Controlo de taxa de manutenção
            $table->unsignedSmallInteger('maintenance_paid_year')->nullable()->after('monthly_target_aoa');
            $table->string('maintenance_status', 32)->default('ok')->after('maintenance_paid_year');

            // Notas internas
            $table->text('notes')->nullable()->after('maintenance_status');
        });
    }

    public function down(): void
    {
        Schema::table('reseller_applications', function (Blueprint $table) {
            $table->dropColumn([
                'reseller_mode',
                'installation_fee_aoa',
                'bonus_vouchers_aoa',
                'monthly_target_aoa',
                'maintenance_paid_year',
                'maintenance_status',
                'notes',
            ]);
        });
    }
};
