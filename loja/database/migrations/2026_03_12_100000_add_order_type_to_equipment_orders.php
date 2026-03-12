<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_orders', function (Blueprint $table) {
            $table->string('order_type')->default('immediate')->after('status');
            $table->date('estimated_delivery_date')->nullable()->after('order_type');
        });
    }

    public function down(): void
    {
        Schema::table('equipment_orders', function (Blueprint $table) {
            $table->dropColumn(['order_type', 'estimated_delivery_date']);
        });
    }
};
