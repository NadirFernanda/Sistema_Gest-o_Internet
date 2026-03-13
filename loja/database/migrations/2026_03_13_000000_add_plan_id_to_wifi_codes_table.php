<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('wifi_codes', function (Blueprint $table) {
            // Identifica o plano ao qual pertence o código: 'diario', 'semanal' ou 'mensal'.
            // nullable() para não quebrar códigos já existentes no stock — o admin
            // deve reatribuí-los ou eliminá-los antes de os usar.
            $table->string('plan_id')->nullable()->after('code')->index();
        });
    }

    public function down(): void
    {
        Schema::table('wifi_codes', function (Blueprint $table) {
            $table->dropColumn('plan_id');
        });
    }
};
