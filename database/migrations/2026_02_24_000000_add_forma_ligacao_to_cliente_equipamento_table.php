<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('cliente_equipamento', function (Blueprint $table) {
            $table->string('forma_ligacao')->nullable()->after('quantidade');
        });
    }

    public function down()
    {
        Schema::table('cliente_equipamento', function (Blueprint $table) {
            $table->dropColumn('forma_ligacao');
        });
    }
};
