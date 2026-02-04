<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (Schema::hasColumn('clientes', 'plano')) {
                $table->dropColumn('plano');
            }
            if (Schema::hasColumn('clientes', 'estado')) {
                $table->dropColumn('estado');
            }
            if (Schema::hasColumn('clientes', 'data_ativacao')) {
                $table->dropColumn('data_ativacao');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('plano')->nullable();
            $table->string('estado')->default('Ativo');
        });
    }
};
