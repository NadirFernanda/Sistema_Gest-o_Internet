<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona approved_at para registar o momento exacto de aprovação
     * da candidatura de revendedor.
     *
     * Usado para aplicar o período de graça: no mês de aprovação a taxa
     * de manutenção não é cobrada — começa a contar a partir do mês seguinte.
     */
    public function up(): void
    {
        Schema::table('reseller_applications', function (Blueprint $table) {
            $table->timestamp('approved_at')
                  ->nullable()
                  ->after('notified_at')
                  ->comment('Data/hora de aprovação da candidatura; null se ainda não aprovada');
        });
    }

    public function down(): void
    {
        Schema::table('reseller_applications', function (Blueprint $table) {
            $table->dropColumn('approved_at');
        });
    }
};
