<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('planos', function (Blueprint $table) {
            $table->string('estado')->default('Ativo');
            $table->date('data_ativacao')->nullable();
        });
        Schema::table('clientes', function (Blueprint $table) {
            $drop = [];
            if (Schema::hasColumn('clientes', 'plano')) $drop[] = 'plano';
            if (Schema::hasColumn('clientes', 'estado')) $drop[] = 'estado';
            if (count($drop)) $table->dropColumn($drop);
        });
    }

    public function down(): void
    {
        Schema::table('planos', function (Blueprint $table) {
            $table->dropColumn(['estado', 'data_ativacao']);
        });
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('plano')->nullable();
            $table->date('data_ativacao')->nullable();
            $table->string('estado')->default('Ativo');
        });
    }
};
