<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('autovenda_orders', function (Blueprint $table) {
            $table->id();

            // Snapshot do plano no momento da compra
            $table->string('plan_id'); // id interno da config (ex.: hora, diario, semanal, mensal)
            $table->string('plan_name');
            $table->string('plan_speed')->nullable();
            $table->integer('plan_duration_minutes')->nullable();
            $table->unsignedInteger('quantity')->default(1); // sempre 1 na autovenda, mas deixamos explícito
            $table->unsignedBigInteger('amount_aoa'); // valor total em Kz
            $table->string('currency', 3)->default('AOA');

            // Dados do cliente
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->string('customer_nif')->nullable();

            // Estado e pagamento
            $table->string('status', 32)->default('pending'); // pending, awaiting_payment, paid, cancelled, failed, expired
            $table->string('payment_method', 32)->nullable(); // multicaixa_express, paypal, etc.
            $table->string('payment_reference')->nullable(); // referência ou id do gateway
            $table->string('payment_gateway')->nullable();
            $table->timestamp('paid_at')->nullable();

            // Entrega do código WiFi
            $table->string('wifi_code')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->boolean('delivered_via_email')->default(false);
            $table->boolean('delivered_via_whatsapp')->default(false);

            // Informação adicional técnica
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('autovenda_orders');
    }
};
