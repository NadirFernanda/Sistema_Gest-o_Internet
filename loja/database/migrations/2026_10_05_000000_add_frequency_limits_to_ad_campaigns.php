<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ad_campaigns', function (Blueprint $table) {
            $table->string('frequency_period', 10)->nullable()->after('ends_at');
            $table->unsignedInteger('frequency_limit')->nullable()->after('frequency_period');
        });

        Schema::create('ad_campaign_period_impressions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_campaign_id')->constrained()->cascadeOnDelete();
            $table->string('frequency_period', 10);
            $table->timestamp('period_start');
            $table->unsignedInteger('impressions_count')->default(0);
            $table->timestamps();
            $table->unique(['ad_campaign_id', 'frequency_period', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_campaign_period_impressions');

        Schema::table('ad_campaigns', function (Blueprint $table) {
            $table->dropColumn(['frequency_period', 'frequency_limit']);
        });
    }
};
