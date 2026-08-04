<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wifi_codes', function (Blueprint $table) {
            $table->uuid('reseller_sale_group')->nullable()->after('reseller_customer_ref')->index();
        });
    }

    public function down(): void
    {
        Schema::table('wifi_codes', function (Blueprint $table) {
            $table->dropColumn('reseller_sale_group');
        });
    }
};
