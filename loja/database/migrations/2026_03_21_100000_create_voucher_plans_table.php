<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();          // diario, semanal, mensal
            $table->string('name');                    // Plano Diário, etc.
            $table->string('validity_label');          // "1 dia", "7 dias", "30 dias"
            $table->unsignedInteger('validity_minutes');
            $table->string('speed_label')->nullable(); // "5 Mbps", "10 Mbps"
            $table->unsignedInteger('price_public_aoa');   // preço ao cliente final
            $table->unsignedInteger('price_reseller_aoa'); // preço que o revendedor paga
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_plans');
    }
};
