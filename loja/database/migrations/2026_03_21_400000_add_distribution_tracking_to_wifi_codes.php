<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wifi_codes', function (Blueprint $table) {
            // Rastreio da distribuição do revendedor ao cliente final
            $table->timestamp('reseller_distributed_at')->nullable()->after('reseller_purchase_id');
            $table->string('reseller_customer_ref', 255)->nullable()->after('reseller_distributed_at')
                  ->comment('Nome/telefone/referência do cliente final a quem o revendedor vendeu');
        });
    }

    public function down(): void
    {
        Schema::table('wifi_codes', function (Blueprint $table) {
            $table->dropColumn(['reseller_distributed_at', 'reseller_customer_ref']);
        });
    }
};
