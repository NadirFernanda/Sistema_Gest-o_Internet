<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('planos', function (Blueprint $table) {
            $table->unsignedBigInteger('template_id')->nullable()->after('id');
            $table->foreign('template_id')->references('id')->on('plan_templates')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('planos', function (Blueprint $table) {
            $table->dropForeign([ 'template_id' ]);
            $table->dropColumn('template_id');
        });
    }
};
