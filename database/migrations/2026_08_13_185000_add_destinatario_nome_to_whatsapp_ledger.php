<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_ledger', function (Blueprint $table) {
            $table->string('destinatario_nome', 100)->nullable()->after('destinatario');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_ledger', function (Blueprint $table) {
            $table->dropColumn('destinatario_nome');
        });
    }
};
