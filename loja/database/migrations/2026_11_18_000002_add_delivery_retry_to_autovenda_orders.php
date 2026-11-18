<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('autovenda_orders', function (Blueprint $table) {
            $table->unsignedTinyInteger('delivery_retry_count')->default(0);
            $table->timestamp('delivery_next_attempt_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('autovenda_orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_retry_count', 'delivery_next_attempt_at']);
        });
    }
};
