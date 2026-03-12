<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona o campo internet_type à tabela reseller_applications.
     *
     * Valores:
     *   'own'         — Revendedor tem internet própria no local de instalação.
     *   'angolawifi'  — Revendedor depende de ligação fornecida pela AngolaWiFi.
     *
     * Nullable para compatibilidade com candidaturas já submetidas antes desta coluna existir.
     */
    public function up(): void
    {
        Schema::table('reseller_applications', function (Blueprint $table) {
            $table->string('internet_type', 32)->nullable()->after('installation_location');
        });
    }

    public function down(): void
    {
        Schema::table('reseller_applications', function (Blueprint $table) {
            $table->dropColumn('internet_type');
        });
    }
};
