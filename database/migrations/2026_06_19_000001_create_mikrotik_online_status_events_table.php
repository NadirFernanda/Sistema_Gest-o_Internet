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
        Schema::create('mikrotik_online_status_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plano_id')->constrained('planos')->onDelete('cascade');
            $table->foreignId('mikrotik_online_status_id')->constrained('mikrotik_online_statuses')->onDelete('cascade');
            $table->enum('event_type', ['online', 'offline']); // Tipo de evento
            $table->timestamp('occurred_at'); // Quando o evento aconteceu
            $table->integer('duration_seconds')->nullable(); // Se offline: quanto tempo durou
            $table->string('disconnect_reason')->nullable(); // Razão da desconexão
            $table->timestamps();

            // Índices para query rápida
            $table->index(['plano_id', 'occurred_at']);
            $table->index(['occurred_at']);
            $table->index('event_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mikrotik_online_status_events');
    }
};
