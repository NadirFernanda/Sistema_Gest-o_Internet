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
        Schema::table('installation_appointments', function (Blueprint $table) {
            $table->string('email', 254)->nullable()->after('phone');
            $table->string('nif', 50)->nullable()->after('email');
            $table->string('morada', 500)->nullable()->after('nif');
        });
    }

    public function down(): void
    {
        Schema::table('installation_appointments', function (Blueprint $table) {
            $table->dropColumn(['email', 'nif', 'morada']);
        });
    }
};
