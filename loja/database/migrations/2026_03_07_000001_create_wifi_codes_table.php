<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wifi_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('status', ['available', 'used', 'reserved'])->default('available');
            $table->unsignedBigInteger('autovenda_order_id')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->foreign('autovenda_order_id')
                ->references('id')->on('autovenda_orders')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wifi_codes');
    }
};
