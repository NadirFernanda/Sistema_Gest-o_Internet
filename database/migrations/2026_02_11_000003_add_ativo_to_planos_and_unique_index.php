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
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            // create index if not exists
            $indexName = 'planos_cliente_nome_ativo_unique';
            $has = false;
            foreach ($sm->listTableIndexes('planos') as $idx) {
                if ($idx->getName() === $indexName) { $has = true; break; }
            }
            if (!$has) {
                $table->unique(['cliente_id','nome','ativo'], $indexName);
            }
        });
    }

    public function down()
    {
        Schema::table('planos', function (Blueprint $table) {
            $indexName = 'planos_cliente_nome_ativo_unique';
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            try {
                $table->dropUnique($indexName);
            } catch (\Exception $e) {
                // ignore
            }
            if (Schema::hasColumn('planos', 'ativo')) $table->dropColumn('ativo');
        });
    }
};
