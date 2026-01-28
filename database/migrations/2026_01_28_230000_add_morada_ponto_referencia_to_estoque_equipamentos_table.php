<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('estoque_equipamentos', function (Blueprint $table) {
            $table->string('morada')->nullable()->after('quantidade');
            $table->string('ponto_referencia')->nullable()->after('morada');
        });
    }

    public function down()
    {
        Schema::table('estoque_equipamentos', function (Blueprint $table) {
            $table->dropColumn(['morada', 'ponto_referencia']);
        });
    }
};
