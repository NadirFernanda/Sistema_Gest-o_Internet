<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabela para pedidos de adesão a planos familiares e empresariais.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * ÂMBITO: PLANOS FAMILIARES & EMPRESARIAIS (carregados do SG via API)
 *
 * NÃO CONFUNDIR com autovenda_orders (planos individuais).
 *  - Planos individuais: sem dados pessoais, sem integração SG, código WiFi imediato.
 *  - Planos familiares/empresariais: com identificação do cliente, coordenados
 *    com o Sistema de Gestão (SG) para activação e gestão da janela de acesso.
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * Fluxo:
 *   1. Cliente na loja clica "Comprar" num plano familiar/empresarial.
 *   2. É redirecionado para o checkout com formulário de identificação.
 *   3. Preenche dados + escolhe método de pagamento.
 *   4. O pedido é registado aqui (status: pending).
 *   5. Admin verifica pagamento e activa o plano no SG.
 *   6. Status atualizado para: confirmed → activated.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('family_plan_requests', function (Blueprint $table) {
            $table->id();

            // Dados do plano (snapshot do SG no momento do pedido)
            $table->string('plan_id');          // id do plano no SG (ex: fam_10mb_3users)
            $table->string('plan_name');
            $table->unsignedBigInteger('plan_preco')->nullable(); // preço em AOA
            $table->unsignedInteger('plan_ciclo_dias')->nullable(); // duração em dias

            // Dados do cliente (obrigatório — diferente dos planos individuais)
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->string('customer_nif')->nullable();

            // Pagamento
            $table->string('payment_method', 32); // multicaixa_express | paypal
            $table->string('payment_reference')->nullable();

            // Estado: pending | confirmed | activated | cancelled
            $table->string('status', 32)->default('pending');

            // Notas internas
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_plan_requests');
    }
};
