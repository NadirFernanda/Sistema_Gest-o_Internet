<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mikrotik_bandwidth_samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plano_id')->constrained()->onDelete('cascade');
            $table->timestamp('sampled_at');
            $table->bigInteger('rx_bytes')->default(0); // download cumulativo
            $table->bigInteger('tx_bytes')->default(0); // upload cumulativo
            $table->integer('rx_rate')->default(0);     // download em bps
            $table->integer('tx_rate')->default(0);     // upload em bps
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['plano_id', 'sampled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mikrotik_bandwidth_samples');
    }
};
