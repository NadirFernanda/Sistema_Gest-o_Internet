<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure plans display in the correct order: Diário → Semanal → Mensal
        DB::table('voucher_plans')->where('slug', 'diario') ->update(['sort_order' => 1]);
        DB::table('voucher_plans')->where('slug', 'semanal')->update(['sort_order' => 2]);
        DB::table('voucher_plans')->where('slug', 'mensal') ->update(['sort_order' => 3]);
    }

    public function down(): void
    {
        DB::table('voucher_plans')->where('slug', 'mensal') ->update(['sort_order' => 1]);
        DB::table('voucher_plans')->where('slug', 'diario') ->update(['sort_order' => 2]);
        DB::table('voucher_plans')->where('slug', 'semanal')->update(['sort_order' => 3]);
    }
};
