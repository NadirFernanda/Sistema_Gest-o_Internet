<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cobranca_id')->constrained('cobrancas')->cascadeOnDelete();
            $table->string('gateway_transaction_id')->nullable()->comment('ID da transação no Pay4All');
            $table->string('merchant_transaction_id', 15)->unique()->comment('ID único da transação gerado localmente');
            $table->decimal('valor', 10, 2);
            $table->string('moeda', 3)->default('AOA');
            $table->string('telefone', 20)->comment('Número de telemóvel cobrado');
            $table->enum('status', ['pendente', 'processando', 'aprovado', 'recusado', 'expirado', 'erro'])
                  ->default('pendente');
            $table->string('gateway_status')->nullable()->comment('Status original do gateway');
            $table->string('gateway_code')->nullable()->comment('Código de resposta do gateway');
            $table->text('gateway_message')->nullable()->comment('Mensagem de resposta do gateway');
            $table->json('gateway_payload')->nullable()->comment('Payload completo do webhook');
            $table->timestamp('processado_em')->nullable()->comment('Quando o webhook foi recebido');
            $table->timestamps();

            $table->index(['cobranca_id', 'status']);
            $table->index('merchant_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
    }
};
