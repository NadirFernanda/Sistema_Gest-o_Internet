<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // admin que criou
            $table->string('assunto');
            $table->enum('categoria', ['Técnico', 'Cobrança', 'Equipamento', 'Plano', 'Outro'])->default('Outro');
            $table->enum('prioridade', ['Baixa', 'Normal', 'Alta', 'Urgente'])->default('Normal');
            $table->enum('estado', ['Aberto', 'Em Andamento', 'Resolvido', 'Fechado'])->default('Aberto');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
