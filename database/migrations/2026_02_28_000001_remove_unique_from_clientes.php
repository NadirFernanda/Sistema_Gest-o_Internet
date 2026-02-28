<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Drop unique indexes if they exist
            try {
                $table->dropUnique(['email']);
            } catch (\Exception $e) {
                // ignore if index doesn't exist
            }
            try {
                $table->dropUnique(['contato']);
            } catch (\Exception $e) {
                // ignore if index doesn't exist
            }
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->unique('email');
            $table->unique('contato');
        });
    }
};
