<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Se support_tickets já existe, não há nada a fazer
        if (Schema::hasTable('support_tickets')) {
            return;
        }

        // Renomear tickets → support_tickets (e ticket_replies → support_ticket_replies)
        if (Schema::hasTable('tickets')) {
            // Dropar FK antes do rename (PostgreSQL exige)
            if (Schema::hasTable('ticket_replies')) {
                Schema::table('ticket_replies', function (Blueprint $table) {
                    try { $table->dropForeign(['ticket_id']); } catch (\Throwable $e) {}
                });
                Schema::rename('ticket_replies', 'support_ticket_replies');
            }

            Schema::rename('tickets', 'support_tickets');

            // Recriar FK após rename
            if (Schema::hasTable('support_ticket_replies')) {
                Schema::table('support_ticket_replies', function (Blueprint $table) {
                    $table->foreign('ticket_id')
                          ->references('id')
                          ->on('support_tickets')
                          ->cascadeOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('support_tickets')) {
            return;
        }

        if (Schema::hasTable('support_ticket_replies')) {
            Schema::table('support_ticket_replies', function (Blueprint $table) {
                try { $table->dropForeign(['ticket_id']); } catch (\Throwable $e) {}
            });
            Schema::rename('support_ticket_replies', 'ticket_replies');
        }

        Schema::rename('support_tickets', 'tickets');

        if (Schema::hasTable('ticket_replies')) {
            Schema::table('ticket_replies', function (Blueprint $table) {
                $table->foreign('ticket_id')
                      ->references('id')
                      ->on('tickets')
                      ->cascadeOnDelete();
            });
        }
    }
};
