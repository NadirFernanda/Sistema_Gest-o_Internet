<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Idempotent: only adds the column if missing.
     *
     * @return void
     */
    public function up(): void
    {
        if (! Schema::hasTable('planos')) {
            return;
        }

        if (! Schema::hasColumn('planos', 'proxima_renovacao')) {
            Schema::table('planos', function (Blueprint $table) {
                $table->date('proxima_renovacao')->nullable()->after('updated_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     * Drops the column only if it exists.
     *
     * @return void
     */
    public function down(): void
    {
        if (! Schema::hasTable('planos')) {
            return;
        }

        if (Schema::hasColumn('planos', 'proxima_renovacao')) {
            Schema::table('planos', function (Blueprint $table) {
                $table->dropColumn('proxima_renovacao');
            });
        }
    }
};
