<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('autovenda_orders', function (Blueprint $table) {
            $table->string('bonus_plan_validity')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('autovenda_orders', function (Blueprint $table) {
            $table->dropColumn('bonus_plan_validity');
        });
    }
};
