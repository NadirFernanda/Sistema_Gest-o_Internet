<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds maintenance_paid_month (1–12) to support monthly maintenance fee tracking.
     * Previously only the year was stored (maintenance_paid_year).
     */
    public function up(): void
    {
        Schema::table('reseller_applications', function (Blueprint $table) {
            $table->unsignedTinyInteger('maintenance_paid_month')
                  ->nullable()
                  ->after('maintenance_paid_year')
                  ->comment('Month (1–12) in which the monthly maintenance fee was last paid');
        });
    }

    public function down(): void
    {
        Schema::table('reseller_applications', function (Blueprint $table) {
            $table->dropColumn('maintenance_paid_month');
        });
    }
};
