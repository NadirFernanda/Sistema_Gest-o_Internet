<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reseller_applications', function (Blueprint $table) {
            $table->unsignedBigInteger('saldo_bonus_aoa')->default(0)->after('bonus_vouchers_aoa')
                  ->comment('Saldo de bónus acumulado enviado pelo admin (em Kz)');
        });

        Schema::create('reseller_bonus_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_application_id')
                  ->constrained('reseller_applications')
                  ->cascadeOnDelete();
            $table->unsignedBigInteger('amount_aoa');
            $table->string('reason')->nullable()->comment('Motivo/nota do admin');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reseller_bonus_transactions');
        Schema::table('reseller_applications', function (Blueprint $table) {
            $table->dropColumn('saldo_bonus_aoa');
        });
    }
};
