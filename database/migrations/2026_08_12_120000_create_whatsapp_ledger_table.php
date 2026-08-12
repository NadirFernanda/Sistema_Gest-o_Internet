<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_ledger', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['debito', 'credito']);
            // Sempre positivo; o tipo indica se soma ou subtrai do saldo.
            $table->decimal('valor', 10, 2);
            $table->string('descricao');
            $table->string('destinatario', 20)->nullable()->comment('Número WhatsApp, quando aplicável a um débito');
            $table->string('mensagem_tipo', 50)->nullable()->comment('Ex.: vencimento, devolucao, comprovante, teste');
            $table->foreignId('registado_por')->nullable()->constrained('users')->nullOnDelete()
                  ->comment('Utilizador que registou um carregamento manual; nulo para débitos automáticos');
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_ledger');
    }
};
