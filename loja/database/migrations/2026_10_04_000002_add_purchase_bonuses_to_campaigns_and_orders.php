<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ad_campaigns', function (Blueprint $table) {
            $table->string('bonus_plan_slug')->nullable()->index();
        });

        Schema::create('ad_campaign_qualifying_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_campaign_id')->constrained('ad_campaigns')->cascadeOnDelete();
            $table->string('purchase_plan_slug');
            $table->unique(['ad_campaign_id', 'purchase_plan_slug']);
        });

        Schema::table('autovenda_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('bonus_campaign_id')->nullable()->index();
            $table->string('bonus_plan_slug')->nullable();
            $table->string('bonus_plan_name')->nullable();
            $table->string('bonus_wifi_code')->nullable();
            $table->string('bonus_delivery_status', 30)->nullable();
            $table->foreign('bonus_campaign_id')
                ->references('id')->on('ad_campaigns')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('autovenda_orders', function (Blueprint $table) {
            $table->dropForeign(['bonus_campaign_id']);
            $table->dropIndex(['bonus_campaign_id']);
            $table->dropColumn([
                'bonus_campaign_id',
                'bonus_plan_slug',
                'bonus_plan_name',
                'bonus_wifi_code',
                'bonus_delivery_status',
            ]);
        });

        Schema::dropIfExists('ad_campaign_qualifying_plans');

        Schema::table('ad_campaigns', function (Blueprint $table) {
            $table->dropIndex(['bonus_plan_slug']);
            $table->dropColumn('bonus_plan_slug');
        });
    }
};
