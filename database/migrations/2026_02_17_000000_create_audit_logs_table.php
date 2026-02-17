<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->string('actor_name')->nullable();
            $table->string('actor_role')->nullable()->index();
            $table->string('ip')->nullable()->index();
            $table->string('user_agent')->nullable();
            $table->string('module')->nullable()->index();
            $table->string('resource_type')->nullable()->index();
            $table->string('resource_id')->nullable()->index();
            $table->string('action')->index();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('request_id')->nullable()->index();
            $table->string('channel')->nullable()->index();
            $table->string('hmac', 128)->nullable();
            $table->string('prev_hash', 128)->nullable();
            $table->timestamps(6);

            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
