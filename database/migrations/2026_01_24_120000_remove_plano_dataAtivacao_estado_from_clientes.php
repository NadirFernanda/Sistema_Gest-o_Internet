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
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('plano')->nullable();
            $table->date('data_ativacao')->nullable();
            $table->string('estado')->default('Ativo');
        });
    }
};
