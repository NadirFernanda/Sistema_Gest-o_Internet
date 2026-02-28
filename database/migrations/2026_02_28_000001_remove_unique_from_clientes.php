<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Use raw SQL with IF EXISTS to be idempotent on PostgreSQL
        // This avoids migration failures when the named constraints are already absent.
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE clientes DROP CONSTRAINT IF EXISTS clientes_email_unique');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE clientes DROP CONSTRAINT IF EXISTS clientes_contato_unique');
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->unique('email');
            $table->unique('contato');
        });
    }
};
