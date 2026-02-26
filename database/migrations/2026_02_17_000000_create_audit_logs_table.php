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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('chain_index')->index();
            $table->string('prev_hash', 128)->nullable();
            $table->string('hmac', 128);

            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->string('actor_name')->nullable();
            $table->string('actor_role')->nullable()->index();

            $table->string('module')->nullable()->index();
            $table->string('action')->nullable()->index();
            $table->string('resource_type')->nullable()->index();
            $table->string('resource_id')->nullable()->index();

            $table->string('ip')->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable()->index();
            $table->string('channel')->nullable()->index();

            $table->json('payload_before')->nullable();
            $table->json('payload_after')->nullable();
            $table->json('meta')->nullable();

            $table->timestampTz('created_at')->useCurrent();

            // Prevent updates at the DB level by not exposing updated_at, and
            // avoid soft deletes for immutability — application-level controls will handle attempts.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
