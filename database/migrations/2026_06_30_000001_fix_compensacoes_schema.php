<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Se a tabela tem o schema antigo (cliente_id, dias, motivo), migrar para o novo.
        // Se já tem o novo schema (plano_id, dias_compensados), não fazer nada.
        if (! Schema::hasTable('compensacoes')) {
            Schema::create('compensacoes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('plano_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->integer('dias_compensados');
                $table->date('anterior')->nullable();
                $table->date('novo')->nullable();
                $table->timestamps();

                $table->foreign('plano_id')->references('id')->on('planos')->onDelete('cascade');
            });
            return;
        }

        // Tabela existe — garantir que tem as colunas novas
        Schema::table('compensacoes', function (Blueprint $table) {
            if (! Schema::hasColumn('compensacoes', 'plano_id')) {
                $table->unsignedBigInteger('plano_id')->nullable()->after('id');
            }
            if (! Schema::hasColumn('compensacoes', 'dias_compensados')) {
                $table->integer('dias_compensados')->nullable()->after('plano_id');
            }
            if (! Schema::hasColumn('compensacoes', 'anterior')) {
                $table->date('anterior')->nullable()->after('dias_compensados');
            }
            if (! Schema::hasColumn('compensacoes', 'novo')) {
                $table->date('novo')->nullable()->after('anterior');
            }
        });
    }

    public function down(): void
    {
        // não reverter — schema antigo era inconsistente
    }
};
