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
        Schema::table('planos', function (Blueprint $table) {
            $table->string('mikrotik_username')->nullable()->after('proxima_renovacao');
            $table->timestamp('mikrotik_synced_at')->nullable()->after('mikrotik_username');
        });
    }

    public function down(): void
    {
        Schema::table('planos', function (Blueprint $table) {
            $table->dropColumn(['mikrotik_username', 'mikrotik_synced_at']);
        });
    }
};
