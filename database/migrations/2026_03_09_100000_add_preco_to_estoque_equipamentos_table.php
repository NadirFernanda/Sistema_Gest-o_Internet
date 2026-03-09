<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('estoque_equipamentos', function (Blueprint $table) {
            $table->unsignedInteger('preco')->nullable()->after('quantidade')
                ->comment('Preço de venda em Kz (null = consultar preço)');
        });
    }

    public function down(): void
    {
        Schema::table('estoque_equipamentos', function (Blueprint $table) {
            $table->dropColumn('preco');
        });
    }
};
