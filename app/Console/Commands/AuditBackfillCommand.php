<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class AuditBackfillCommand extends Command
{
    protected $signature = 'audit:backfill {--dry-run} {--limit=0} {--force}';
    protected $description = 'Backfill missing chain_index, prev_hash and hmac for existing audit logs.';

    public function handle(): int
    {
        $key = config('app.audit_key') ?? env('AUDIT_HMAC_KEY');
        if (empty($key)) {
            $this->error('AUDIT_HMAC_KEY not configured. Provide the key via environment.');
            return 2;
        }

        $limit = (int) $this->option('limit');
        $dry = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $this->info('Starting audit backfill' . ($dry ? ' (dry-run)' : ''));

        $query = AuditLog::query()->orderBy('chain_index')->orderBy('id');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $prevHmac = '0';
        $i = 0;

        $rows = $query->get();
        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $i++;
                // compute payload used by the job
                $data = [
                    'actor_id' => $row->actor_id ?? $row->user_id ?? null,
                    'actor_name' => $row->actor_name ?? null,
                    'actor_role' => $row->actor_role ?? $row->role ?? null,
                    'module' => $row->module ?? null,
                    'action' => $row->action ?? null,
                    'resource_type' => $row->resource_type ?? $row->auditable_type ?? null,
                    'resource_id' => $row->resource_id ?? $row->auditable_id ?? null,
                    'payload_before' => $row->payload_before ?? $row->old_values ?? null,
                    'payload_after' => $row->payload_after ?? $row->new_values ?? null,
                    'meta' => $row->meta ?? null,
                ];

                $ts = ($row->created_at ? $row->created_at->format(DATE_ATOM) : now()->format(DATE_ATOM));
                $payloadToSign = ['prev' => $prevHmac, 'data' => $data, 'ts' => $ts];
                $toSign = json_encode($payloadToSign, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $hmac = hash_hmac('sha256', $toSign, $key);

                $updates = [];
                if (empty($row->hmac) || $force) {
                    $updates['hmac'] = $hmac;
                }
                if (empty($row->prev_hash) || $force) {
                    $updates['prev_hash'] = $prevHmac;
                }
                if ((empty($row->chain_index) || $row->chain_index === 0) || $force) {
                    $updates['chain_index'] = $i;
                }

                if ($dry) {
                    $h = $updates['hmac'] ?? '[keep]';
                    $p = $updates['prev_hash'] ?? '[keep]';
                    $cidx = $updates['chain_index'] ?? '[keep]';
                    $this->line("[DRY] row {$row->id} -> hmac={$h} prev={$p} chain_index={$cidx}");
                } else {
                    if (! empty($updates)) {
                        AuditLog::where('id', $row->id)->update($updates);
                        $this->info("Updated row {$row->id}");
                    }
                }

                $prevHmac = $updates['hmac'] ?? ($row->hmac ?? $prevHmac);
            }
            if (! $dry) DB::commit();
            else DB::rollBack();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Backfill failed: '.$e->getMessage());
            return 2;
        }

        $this->info('Backfill completed. Processed '.$i.' rows.');
        return 0;
    }
}
