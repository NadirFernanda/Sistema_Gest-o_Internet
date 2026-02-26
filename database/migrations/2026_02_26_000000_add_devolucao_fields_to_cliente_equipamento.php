<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cliente_equipamento', function (Blueprint $table) {
            if (!Schema::hasColumn('cliente_equipamento', 'status')) {
                $table->string('status')->default('emprestado')->after('quantidade');
            }
            if (!Schema::hasColumn('cliente_equipamento', 'devolucao_solicitada_at')) {
                $table->timestamp('devolucao_solicitada_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('cliente_equipamento', 'devolucao_prazo')) {
                $table->date('devolucao_prazo')->nullable()->after('devolucao_solicitada_at');
            }
            if (!Schema::hasColumn('cliente_equipamento', 'motivo_requisicao')) {
                $table->text('motivo_requisicao')->nullable()->after('devolucao_prazo');
            }
        });
    }

    public function down()
    {
        Schema::table('cliente_equipamento', function (Blueprint $table) {
            if (Schema::hasColumn('cliente_equipamento', 'motivo_requisicao')) {
                $table->dropColumn('motivo_requisicao');
            }
            if (Schema::hasColumn('cliente_equipamento', 'devolucao_prazo')) {
                $table->dropColumn('devolucao_prazo');
            }
            if (Schema::hasColumn('cliente_equipamento', 'devolucao_solicitada_at')) {
                $table->dropColumn('devolucao_solicitada_at');
            }
            if (Schema::hasColumn('cliente_equipamento', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
