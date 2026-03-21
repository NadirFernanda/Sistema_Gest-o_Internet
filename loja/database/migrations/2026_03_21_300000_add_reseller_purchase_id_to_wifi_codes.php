<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wifi_codes', function (Blueprint $table) {
            $table->foreignId('reseller_purchase_id')
                  ->nullable()
                  ->constrained('reseller_purchases')
                  ->nullOnDelete()
                  ->after('autovenda_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('wifi_codes', function (Blueprint $table) {
            $table->dropForeign(['reseller_purchase_id']);
            $table->dropColumn('reseller_purchase_id');
        });
    }
};
