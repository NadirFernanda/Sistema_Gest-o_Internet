<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('hmac', 128)->nullable()->after('new_values')->index();
        });
    }

    public function down()
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn('hmac');
        });
    }
};
