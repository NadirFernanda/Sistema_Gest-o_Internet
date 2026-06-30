<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A coluna 'dias' (schema Feb-16) é NOT NULL mas já não é usada.
        // O insert falha porque não enviamos esse campo.
        // Torná-la nullable resolve o problema sem perder dados.
        if (Schema::hasColumn('compensacoes', 'dias')) {
            DB::statement('ALTER TABLE compensacoes ALTER COLUMN dias DROP NOT NULL');
        }
        if (Schema::hasColumn('compensacoes', 'motivo')) {
            DB::statement('ALTER TABLE compensacoes ALTER COLUMN motivo DROP NOT NULL');
        }
        // Garantir que cliente_id também não bloqueia (schema antigo)
        if (Schema::hasColumn('compensacoes', 'cliente_id')) {
            DB::statement('ALTER TABLE compensacoes ALTER COLUMN cliente_id DROP NOT NULL');
        }
    }

    public function down(): void {}
};
