<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reseller_staff')) return;

        Schema::create('reseller_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_application_id')
                  ->constrained('reseller_applications')
                  ->cascadeOnDelete();
            $table->string('full_name');
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->string('pin_hash');
            $table->string('status', 20)->default('active'); // active | suspended
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('reseller_application_id');
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reseller_staff');
    }
};
