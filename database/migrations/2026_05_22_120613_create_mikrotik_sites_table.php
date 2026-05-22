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
        Schema::create('mikrotik_sites', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('localizacao')->nullable();
            $table->string('host');
            $table->unsignedSmallInteger('port')->default(8728);
            $table->string('username')->default('admin');
            $table->string('password');
            $table->string('user_prefix')->default('');
            $table->string('default_profile')->default('default');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mikrotik_sites');
    }
};
