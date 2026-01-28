<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('cliente_equipamento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('estoque_equipamento_id');
            $table->string('morada')->nullable();
            $table->string('ponto_referencia')->nullable();
            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('estoque_equipamento_id')->references('id')->on('estoque_equipamentos')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cliente_equipamento');
    }
};
