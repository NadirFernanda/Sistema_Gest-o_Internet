<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop the BI unique constraint/index if it exists (Postgres)
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE clientes DROP CONSTRAINT IF EXISTS clientes_bi_unique');
        // For other DBs, attempt Schema builder (will be ignored if not applicable)
        try {
            Schema::table('clientes', function (Blueprint $table) {
                $table->dropUnique(['bi']);
            });
        } catch (\Throwable $e) {
            // ignore if not present or unsupported
        }
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->unique('bi');
        });
    }
};
