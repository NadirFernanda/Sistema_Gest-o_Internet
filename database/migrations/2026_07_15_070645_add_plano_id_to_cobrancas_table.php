<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cobrancas', function (Blueprint $table) {
            $table->unsignedBigInteger('plano_id')->nullable()->after('cliente_id');
            $table->foreign('plano_id')->references('id')->on('planos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cobrancas', function (Blueprint $table) {
            $table->dropForeign(['plano_id']);
            $table->dropColumn('plano_id');
        });
    }
};
