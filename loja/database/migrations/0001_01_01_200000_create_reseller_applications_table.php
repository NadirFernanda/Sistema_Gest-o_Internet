<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reseller_applications', function (Blueprint $table) {
            $table->id();

            $table->string('full_name');
            $table->string('document_number'); // Nº do BI ou NIF
            $table->string('address');
            $table->string('email');
            $table->string('phone'); // Telefone / WhatsApp
            $table->string('installation_location'); // Local de Instalação Pretendido
            $table->string('subject');
            $table->text('message');

            $table->string('status', 32)->default('pending'); // pending, approved, rejected
            $table->timestamp('notified_at')->nullable(); // quando os requisitos foram enviados ao candidato

            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reseller_applications');
    }
};
