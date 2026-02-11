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
        // Backfill any null vencimento to created_at date or today if created_at is null
        $cobrancas = DB::table('cobrancas')->select('id', 'created_at')->whereNull('data_vencimento')->get();
        foreach ($cobrancas as $c) {
            $fill = $c->created_at ? Carbon::parse($c->created_at)->toDateString() : Carbon::today()->toDateString();
            DB::table('cobrancas')->where('id', $c->id)->update(['data_vencimento' => $fill]);
        }

        // Alter column to NOT NULL (requires doctrine/dbal when running)
        try {
            Schema::table('cobrancas', function (Blueprint $table) {
                $table->date('data_vencimento')->nullable(false)->change();
            });
        } catch (\Exception $e) {
            \Log::warning('Failed to alter cobrancas.data_vencimento to NOT NULL: ' . $e->getMessage());
        }
    }

    public function down()
    {
        try {
            Schema::table('cobrancas', function (Blueprint $table) {
                $table->date('data_vencimento')->nullable()->change();
            });
        } catch (\Exception $e) {
            \Log::warning('Failed to revert cobrancas.data_vencimento nullability: ' . $e->getMessage());
        }
    }
};
