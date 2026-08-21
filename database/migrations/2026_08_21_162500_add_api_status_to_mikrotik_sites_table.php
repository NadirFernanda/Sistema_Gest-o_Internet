<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mikrotik_sites', function (Blueprint $table) {
            $table->string('api_status')->default('desconhecido')->after('active');
            $table->text('api_last_error')->nullable()->after('api_status');
            $table->timestamp('api_checked_at')->nullable()->after('api_last_error');
        });
    }

    public function down(): void
    {
        Schema::table('mikrotik_sites', function (Blueprint $table) {
            $table->dropColumn(['api_status', 'api_last_error', 'api_checked_at']);
        });
    }
};
