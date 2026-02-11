<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('planos', function (Blueprint $table) {
            if (!Schema::hasColumn('planos', 'ativo')) {
                $table->boolean('ativo')->default(true)->after('estado');
            }
        });

        // Add composite unique index to prevent duplicate active plans with same name per client
        // Unique on (cliente_id, nome, ativo)
        Schema::table('planos', function (Blueprint $table) {
            $indexName = 'planos_cliente_nome_ativo_unique';
            try {
                $table->unique(['cliente_id','nome','ativo'], $indexName);
            } catch (\Exception $e) {
                // index already exists or creating failed on this platform; ignore
            }
        });
    }

    public function down()
    {
        Schema::table('planos', function (Blueprint $table) {
            $indexName = 'planos_cliente_nome_ativo_unique';
            try {
                $table->dropUnique($indexName);
            } catch (\Exception $e) {
                // ignore: index may not exist on this platform
            }
            if (Schema::hasColumn('planos', 'ativo')) {
                $table->dropColumn('ativo');
            }
        });
    }
};
