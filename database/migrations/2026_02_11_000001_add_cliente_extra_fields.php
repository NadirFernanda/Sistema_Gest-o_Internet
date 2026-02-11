<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (!Schema::hasColumn('clientes', 'estado')) {
                $table->string('estado', 32)->default('Ativo')->after('nome');
            }
            if (!Schema::hasColumn('clientes', 'tipo_documento')) {
                $table->string('tipo_documento', 32)->default('BI')->after('estado');
            }
            if (!Schema::hasColumn('clientes', 'numero_documento')) {
                $table->string('numero_documento', 64)->default('')->after('tipo_documento');
            }
            if (!Schema::hasColumn('clientes', 'contato_principal')) {
                $table->string('contato_principal', 32)->nullable()->after('email');
            }
            if (!Schema::hasColumn('clientes', 'contato_whatsapp')) {
                $table->string('contato_whatsapp', 32)->nullable()->after('contato_principal');
            }
            if (!Schema::hasColumn('clientes', 'observacoes')) {
                $table->text('observacoes')->nullable()->after('contato_whatsapp');
            }
            if (!Schema::hasColumn('clientes', 'ambiente')) {
                $table->string('ambiente', 16)->default('production')->after('observacoes');
            }
        });

        // Backfill sensible defaults for existing rows
        \DB::table('clientes')->whereNull('estado')->update(['estado' => 'Ativo']);
    }

    public function down()
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (Schema::hasColumn('clientes', 'ambiente')) $table->dropColumn('ambiente');
            if (Schema::hasColumn('clientes', 'observacoes')) $table->dropColumn('observacoes');
            if (Schema::hasColumn('clientes', 'contato_whatsapp')) $table->dropColumn('contato_whatsapp');
            if (Schema::hasColumn('clientes', 'contato_principal')) $table->dropColumn('contato_principal');
            if (Schema::hasColumn('clientes', 'numero_documento')) $table->dropColumn('numero_documento');
            if (Schema::hasColumn('clientes', 'tipo_documento')) $table->dropColumn('tipo_documento');
            if (Schema::hasColumn('clientes', 'estado')) $table->dropColumn('estado');
        });
    }
};
