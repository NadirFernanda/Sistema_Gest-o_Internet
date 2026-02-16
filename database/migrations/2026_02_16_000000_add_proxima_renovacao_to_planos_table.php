<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('planos', function (Blueprint $table) {
            if (! Schema::hasColumn('planos', 'proxima_renovacao')) {
                $table->date('proxima_renovacao')->nullable()->after('data_ativacao');
            }
        });
    }

    public function down(): void
    {
        Schema::table('planos', function (Blueprint $table) {
            if (Schema::hasColumn('planos', 'proxima_renovacao')) {
                $table->dropColumn('proxima_renovacao');
            }
        });
    }
};
