<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('installation_appointments', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('phone', 30);
            $table->enum('type', ['familia', 'empresa', 'instituicao'])->default('familia');
            $table->text('message')->nullable();

            // pending → contacted → done | cancelled
            $table->string('status', 32)->default('pending');
            $table->text('admin_notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installation_appointments');
    }
};
