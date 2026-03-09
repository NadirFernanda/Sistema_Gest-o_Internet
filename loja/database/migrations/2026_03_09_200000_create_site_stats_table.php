<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('site_stats', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('ordem')->default(0);
            $table->string('valor');                      // display text: "5.000+", "99.8%", "24–48h", "24/7"
            $table->string('legenda');                    // label below: "Clientes activos"
            $table->decimal('count_to', 10, 2)->nullable(); // numeric target for animation (null = no count)
            $table->tinyInteger('count_decimals')->default(0); // decimal places shown during count
            $table->string('count_suffix')->nullable();   // suffix appended after the number: "+", "%"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_stats');
    }
};
