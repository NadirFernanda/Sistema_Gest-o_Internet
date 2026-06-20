<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mikrotik_bandwidth_samples', function (Blueprint $table) {
            $table->string('caller_id', 20)->nullable()->after('ip_address'); // MAC address
            $table->integer('uptime_seconds')->default(0)->after('caller_id');
            $table->integer('max_rx_bps')->default(0)->after('uptime_seconds'); // limite download
            $table->integer('max_tx_bps')->default(0)->after('max_rx_bps');    // limite upload
        });
    }

    public function down(): void
    {
        Schema::table('mikrotik_bandwidth_samples', function (Blueprint $table) {
            $table->dropColumn(['caller_id', 'uptime_seconds', 'max_rx_bps', 'max_tx_bps']);
        });
    }
};
