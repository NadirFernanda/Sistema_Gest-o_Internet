<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->foreignId('mikrotik_site_id')
                  ->nullable()
                  ->constrained('mikrotik_sites')
                  ->nullOnDelete()
                  ->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropForeign(['mikrotik_site_id']);
            $table->dropColumn('mikrotik_site_id');
        });
    }
};
