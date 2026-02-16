<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('compensacoes');
    }
};
