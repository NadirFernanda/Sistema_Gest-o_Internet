<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Use safe ALTER TABLE statements to add columns if they don't exist (works on Postgres/MySQL 8+)
        try {
            \DB::statement("ALTER TABLE cobrancas ADD COLUMN IF NOT EXISTS status varchar(32) DEFAULT 'pendente';");
        } catch (\Exception $e) {
            // Fallback: try via Schema if DB statement fails
            Schema::table('cobrancas', function (Blueprint $table) {
                if (!Schema::hasColumn('cobrancas', 'status')) {
                    $table->string('status', 32)->default('pendente')->after('valor');
                }
            });
        }

        // Prefer existing 'data_vencimento' if present; only add 'vencimento' if neither exists
        $hasDataVenc = false;
        $hasVenc = false;
        try {
            $res = \DB::select("SELECT column_name FROM information_schema.columns WHERE table_name='cobrancas' AND column_name='data_vencimento'");
            $hasDataVenc = count($res) > 0;
        } catch (\Exception $e) {
            $hasDataVenc = Schema::hasColumn('cobrancas', 'data_vencimento');
        }
        try {
            $res2 = \DB::select("SELECT column_name FROM information_schema.columns WHERE table_name='cobrancas' AND column_name='vencimento'");
            $hasVenc = count($res2) > 0;
        } catch (\Exception $e) {
            $hasVenc = Schema::hasColumn('cobrancas', 'vencimento');
        }

        if (! $hasDataVenc && ! $hasVenc) {
            try {
                \DB::statement("ALTER TABLE cobrancas ADD COLUMN IF NOT EXISTS vencimento date;");
            } catch (\Exception $e) {
                Schema::table('cobrancas', function (Blueprint $table) {
                    if (!Schema::hasColumn('cobrancas', 'vencimento')) {
                        $table->date('vencimento')->nullable()->after('status');
                    }
                });
            }
            $vencimentoCol = 'vencimento';
        } elseif ($hasDataVenc) {
            $vencimentoCol = 'data_vencimento';
        } else {
            $vencimentoCol = 'vencimento';
        }

        // Backfill values now that columns exist
        try {
            if (Schema::hasColumn('cobrancas', 'status')) {
                \DB::table('cobrancas')->whereNull('status')->update(['status' => 'pendente']);
            }
        } catch (\Exception $e) {
            \Log::warning('Backfill status failed: '. $e->getMessage());
        }

        try {
            // Double-check the target column actually exists before updating.
            if ($vencimentoCol && Schema::hasColumn('cobrancas', $vencimentoCol)) {
                // Use raw SQL to set the column based on created_at when null
                \DB::table('cobrancas')->whereNull($vencimentoCol)->update([$vencimentoCol => \DB::raw('DATE(created_at)')]);
            } else {
                \Log::info('Skipping cobrancas vencimento backfill: target column not present (checked for '.($vencimentoCol ?? 'none').').');
            }
        } catch (\Exception $e) {
            \Log::warning('Backfill vencimento failed: '. $e->getMessage());
        }
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
