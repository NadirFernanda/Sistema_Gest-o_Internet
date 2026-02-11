<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cobrancas', function (Blueprint $table) {
            if (!Schema::hasColumn('cobrancas', 'status')) {
                $table->string('status', 32)->default('pendente')->after('valor');
            } else {
                \DB::table('cobrancas')->whereNull('status')->update(['status' => 'pendente']);
            }

            if (!Schema::hasColumn('cobrancas', 'vencimento')) {
                $table->date('vencimento')->nullable()->after('status');
            }
            // Backfill vencimento with created_at if missing
            \DB::table('cobrancas')->whereNull('vencimento')->update(['vencimento' => \DB::raw('DATE(created_at)')]);
        });
    }

    public function down()
    {
        Schema::table('cobrancas', function (Blueprint $table) {
            if (Schema::hasColumn('cobrancas', 'vencimento')) {
                // keep vencimento (down migration will not remove to avoid data loss)
            }
            if (Schema::hasColumn('cobrancas', 'status')) {
                // keep status to avoid losing business data
            }
        });
    }
};
