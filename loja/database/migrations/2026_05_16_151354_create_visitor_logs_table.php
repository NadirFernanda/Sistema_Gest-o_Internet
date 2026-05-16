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
        Schema::create('visitor_logs', function (Blueprint $table) {
            $table->date('date');
            $table->unsignedTinyInteger('hour');    // 0–23
            $table->unsignedInteger('sessions')->default(0);  // unique sessions in that hour
            $table->unsignedInteger('hits')->default(0);      // total page hits
            $table->primary(['date', 'hour']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
    }
};
