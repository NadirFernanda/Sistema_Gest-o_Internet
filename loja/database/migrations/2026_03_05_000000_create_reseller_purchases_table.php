<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reseller_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reseller_application_id');
            $table->unsignedBigInteger('gross_amount_aoa');
            $table->unsignedTinyInteger('discount_percent')->default(0);
            $table->unsignedBigInteger('net_amount_aoa');
            $table->unsignedInteger('codes_count');
            $table->string('csv_path');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('reseller_application_id')->references('id')->on('reseller_applications')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reseller_purchases');
    }
};
