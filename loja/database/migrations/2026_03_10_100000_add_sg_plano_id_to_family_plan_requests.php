<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds sg_plano_id to family_plan_requests.
 *
 * When the loja registers a plan purchase it immediately creates the plan on the
 * SG as 'Pendente'. This column stores the SG's plano.id so that the payment
 * webhook can call /api/janela-activar/{sg_plano_id} to flip the state to 'Ativo'
 * without having to re-create the client or plan.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('family_plan_requests', function (Blueprint $table) {
            // SG plano ID created during checkout (status: Pendente).
            // Null if the SG was unreachable at checkout time.
            $table->unsignedBigInteger('sg_plano_id')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('family_plan_requests', function (Blueprint $table) {
            $table->dropColumn('sg_plano_id');
        });
    }
};
