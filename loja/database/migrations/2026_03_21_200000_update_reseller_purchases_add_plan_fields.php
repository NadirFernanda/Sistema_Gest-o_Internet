<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reseller_purchases', function (Blueprint $table) {
            $table->foreignId('voucher_plan_id')->nullable()->constrained('voucher_plans')->nullOnDelete()->after('reseller_application_id');
            $table->string('plan_slug')->nullable()->after('voucher_plan_id');
            $table->string('plan_name')->nullable()->after('plan_slug');
            $table->unsignedInteger('quantity')->nullable()->after('plan_name');
            $table->unsignedInteger('unit_price_aoa')->nullable()->after('quantity');  // preço pago por voucher
            $table->unsignedInteger('profit_aoa')->nullable()->after('unit_price_aoa'); // lucro potencial total
            $table->string('status')->default('completed')->after('csv_path');
        });
    }

    public function down(): void
    {
        Schema::table('reseller_purchases', function (Blueprint $table) {
            $table->dropForeign(['voucher_plan_id']);
            $table->dropColumn(['voucher_plan_id','plan_slug','plan_name','quantity','unit_price_aoa','profit_aoa','status']);
        });
    }
};
