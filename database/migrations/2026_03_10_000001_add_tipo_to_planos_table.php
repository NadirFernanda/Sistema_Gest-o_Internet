<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Adiciona como nullable
        Schema::table('planos', function (Blueprint $table) {
            $table->enum('tipo', ['familiar', 'institucional', 'empresarial', 'site'])->nullable()->after('descricao');
        });

        // 2. Atualiza todos os registros existentes para 'familiar'
        \DB::table('planos')->whereNull('tipo')->update(['tipo' => 'familiar']);

        // 3. Torna o campo obrigatório (NOT NULL) via SQL puro para compatibilidade PostgreSQL
        $driver = \DB::getDriverName();
        if ($driver === 'pgsql') {
            \DB::statement("ALTER TABLE planos ALTER COLUMN tipo SET NOT NULL;");
        } else {
            Schema::table('planos', function (Blueprint $table) {
                $table->enum('tipo', ['familiar', 'institucional', 'empresarial', 'site'])->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('planos', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
