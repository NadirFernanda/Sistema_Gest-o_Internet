<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new columns if they do not exist (safe for production)
        Schema::table('audit_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_logs', 'chain_index')) {
                $table->unsignedBigInteger('chain_index')->nullable()->after('id')->index();
            }
            if (! Schema::hasColumn('audit_logs', 'prev_hash')) {
                $table->string('prev_hash', 128)->nullable()->after('chain_index');
            }
            if (! Schema::hasColumn('audit_logs', 'hmac')) {
                $table->string('hmac', 128)->nullable()->after('prev_hash')->index();
            }
            if (! Schema::hasColumn('audit_logs', 'payload_before')) {
                $table->json('payload_before')->nullable()->after('channel');
            }
            if (! Schema::hasColumn('audit_logs', 'payload_after')) {
                $table->json('payload_after')->nullable()->after('payload_before');
            }
            if (! Schema::hasColumn('audit_logs', 'meta')) {
                $table->json('meta')->nullable()->after('payload_after');
            }
        });

        // Backfill chain_index for existing rows where possible using id as a best-effort
        try {
            DB::statement(<<<'SQL'
                UPDATE audit_logs
                SET chain_index = id
                WHERE chain_index IS NULL
            SQL);
        } catch (\Exception $e) {
            // ignore backfill errors — it's best-effort and can be run manually later
            logger()->warning('Backfill chain_index failed: '.$e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'meta')) {
                $table->dropColumn('meta');
            }
            if (Schema::hasColumn('audit_logs', 'payload_after')) {
                $table->dropColumn('payload_after');
            }
            if (Schema::hasColumn('audit_logs', 'payload_before')) {
                $table->dropColumn('payload_before');
            }
            if (Schema::hasColumn('audit_logs', 'hmac')) {
                $table->dropIndex(['hmac']);
                $table->dropColumn('hmac');
            }
            if (Schema::hasColumn('audit_logs', 'prev_hash')) {
                $table->dropColumn('prev_hash');
            }
            if (Schema::hasColumn('audit_logs', 'chain_index')) {
                $table->dropIndex(['chain_index']);
                $table->dropColumn('chain_index');
            }
        });
    }
};
