<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('ticket_mensagens');
        Schema::dropIfExists('tickets');
    }

    public function down(): void {}
};
