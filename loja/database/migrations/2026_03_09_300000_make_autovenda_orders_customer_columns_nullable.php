<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Torna nullable os campos de dados do cliente em autovenda_orders.
 *
 * Para planos individuais rápidos (Dia, Semana, Mês) não são recolhidos dados
 * pessoais — o cliente compra sem fornecer nome, e-mail ou telefone.
 * A migração original criou estas colunas como NOT NULL, o que causava um erro
 * 500 ao criar ordens sem dados de cliente.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('autovenda_orders', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->change();
            $table->string('customer_email')->nullable()->change();
            $table->string('customer_phone')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('autovenda_orders', function (Blueprint $table) {
            // Reverte para NOT NULL — pode falhar se já existirem registos com NULL
            $table->string('customer_name')->nullable(false)->change();
            $table->string('customer_email')->nullable(false)->change();
            $table->string('customer_phone')->nullable(false)->change();
        });
    }
};
