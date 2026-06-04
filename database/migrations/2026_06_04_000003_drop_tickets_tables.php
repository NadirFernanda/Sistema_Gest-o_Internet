<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('ticket_mensagens');
        Schema::dropIfExists('ticket_replies');
        \Illuminate\Support\Facades\DB::statement('DROP TABLE IF EXISTS tickets CASCADE');
    }

    public function down(): void {}
};
