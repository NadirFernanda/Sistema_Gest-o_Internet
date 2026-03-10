<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('plan_templates', function (Blueprint $table) {
            $table->string('tipo')->nullable()->after('estado');
        });
    }

    public function down()
    {
        Schema::table('plan_templates', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
