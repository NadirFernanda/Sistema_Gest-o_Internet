<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    public function up()
    {
        // Backfill any null activation dates to today to avoid blocking schema change
        DB::table('planos')->whereNull('data_ativacao')->update(['data_ativacao' => Carbon::today()->toDateString()]);

        // Alter column to NOT NULL (requires doctrine/dbal when running)
        try {
            Schema::table('planos', function (Blueprint $table) {
                $table->date('data_ativacao')->nullable(false)->change();
            });
        } catch (\Exception $e) {
            // If change fails (missing doctrine/dbal), log and continue — admin must run with dbal installed
            \Log::warning('Failed to alter planos.data_ativacao to NOT NULL: ' . $e->getMessage());
        }
    }

    public function down()
    {
        try {
            Schema::table('planos', function (Blueprint $table) {
                $table->date('data_ativacao')->nullable()->change();
            });
        } catch (\Exception $e) {
            \Log::warning('Failed to revert planos.data_ativacao nullability: ' . $e->getMessage());
        }
    }
};
