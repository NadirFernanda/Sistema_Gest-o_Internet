<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('wifi_codes', 'reseller_staff_id')) return;

        Schema::table('wifi_codes', function (Blueprint $table) {
            $table->foreignId('reseller_staff_id')
                  ->nullable()
                  ->after('reseller_purchase_id')
                  ->constrained('reseller_staff')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('wifi_codes', function (Blueprint $table) {
            $table->dropForeign(['reseller_staff_id']);
            $table->dropColumn('reseller_staff_id');
        });
    }
};
