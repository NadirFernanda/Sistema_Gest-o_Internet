<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('deletion_audits', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('reason')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('deletion_audits');
    }
};
