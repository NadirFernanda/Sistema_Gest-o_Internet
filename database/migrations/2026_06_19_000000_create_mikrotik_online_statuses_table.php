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
        Schema::create('mikrotik_online_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plano_id')->constrained('planos')->onDelete('cascade');
            $table->foreignId('mikrotik_site_id')->constrained('mikrotik_sites')->onDelete('cascade');
            
            // Current status
            $table->boolean('is_online')->default(false)->index();
            
            // Status tracking timestamps
            $table->dateTime('last_seen_online_at')->nullable();
            $table->dateTime('last_seen_offline_at')->nullable();
            
            // Downtime stats
            $table->integer('total_downtime_seconds')->default(0);
            $table->string('disconnect_reason')->nullable();
            
            // Metadata
            $table->timestamps();
            
            // Indexes for fast queries
            $table->index(['plano_id', 'is_online']);
            $table->index(['mikrotik_site_id', 'is_online']);
            $table->index('last_seen_offline_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mikrotik_online_statuses');
    }
};
