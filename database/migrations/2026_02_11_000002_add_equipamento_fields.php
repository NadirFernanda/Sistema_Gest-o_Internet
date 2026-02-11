<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('equipamentos', function (Blueprint $table) {
            if (!Schema::hasColumn('equipamentos', 'marca')) {
                $table->string('marca', 128)->nullable()->after('nome');
            }
            if (!Schema::hasColumn('equipamentos', 'modelo')) {
                $table->string('modelo', 128)->nullable()->after('marca');
            }
            if (!Schema::hasColumn('equipamentos', 'serial_number')) {
                $table->string('serial_number', 128)->nullable()->after('modelo');
            }
            if (!Schema::hasColumn('equipamentos', 'mac_address')) {
                $table->string('mac_address', 64)->nullable()->after('serial_number');
            }
            if (!Schema::hasColumn('equipamentos', 'localizacao')) {
                $table->string('localizacao', 255)->nullable()->after('mac_address');
            }
            if (!Schema::hasColumn('equipamentos', 'referencia')) {
                $table->string('referencia', 255)->nullable()->after('localizacao');
            }
            if (!Schema::hasColumn('equipamentos', 'quantidade')) {
                $table->integer('quantidade')->default(1)->after('referencia');
            }
            if (!Schema::hasColumn('equipamentos', 'estado')) {
                $table->string('estado', 32)->default('Ativo')->after('quantidade');
            }
        });
    }

    public function down()
    {
        Schema::table('equipamentos', function (Blueprint $table) {
            if (Schema::hasColumn('equipamentos', 'estado')) $table->dropColumn('estado');
            if (Schema::hasColumn('equipamentos', 'quantidade')) $table->dropColumn('quantidade');
            if (Schema::hasColumn('equipamentos', 'referencia')) $table->dropColumn('referencia');
            if (Schema::hasColumn('equipamentos', 'localizacao')) $table->dropColumn('localizacao');
            if (Schema::hasColumn('equipamentos', 'mac_address')) $table->dropColumn('mac_address');
            if (Schema::hasColumn('equipamentos', 'serial_number')) $table->dropColumn('serial_number');
            if (Schema::hasColumn('equipamentos', 'modelo')) $table->dropColumn('modelo');
            if (Schema::hasColumn('equipamentos', 'marca')) $table->dropColumn('marca');
        });
    }
};
