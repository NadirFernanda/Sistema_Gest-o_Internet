<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reseller_purchases', function (Blueprint $table) {
            // Impostos retidos (6,5% do lucro bruto)
            $table->unsignedBigInteger('tax_aoa')->default(0)->after('profit_aoa');
        });
    }

    public function down(): void
    {
        Schema::table('reseller_purchases', function (Blueprint $table) {
            $table->dropColumn('tax_aoa');
        });
    }
};
