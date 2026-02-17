<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class AuditVerifyCommand extends Command
{
    protected $signature = 'audit:verify {--limit=1000}';
    protected $description = 'Verifies HMAC chain integrity for audit logs (reports mismatches).';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $this->info('Starting audit verification...');

        $lastHash = '0';
        $cursor = 0;
        $errors = 0;

        AuditLog::orderBy('chain_index')->chunk(500, function ($rows) use (&$lastHash, &$errors) {
            foreach ($rows as $row) {
                $payload = json_encode(['prev' => $row->prev_hash ?? '0', 'data' => [
                    'actor_id' => $row->actor_id,
                    'actor_name' => $row->actor_name,
                    'actor_role' => $row->actor_role,
                    'module' => $row->module,
                    'action' => $row->action,
                    'resource_type' => $row->resource_type,
                    'resource_id' => $row->resource_id,
                    'payload_before' => $row->payload_before,
                    'payload_after' => $row->payload_after,
                ]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $key = config('app.audit_key') ?? env('AUDIT_HMAC_KEY');
                $computed = $key ? hash_hmac('sha256', $payload, $key) : null;
                if ($computed !== $row->hmac) {
                    $this->error("Mismatch at chain_index {$row->chain_index} (id {$row->id})");
                    $errors++;
                }
                $lastHash = $row->hmac;
            }
        });

        if ($errors === 0) {
            $this->info('Audit chain OK');
            return 0;
        }

        $this->error("{$errors} mismatches detected");
        return 2;
    }
}
