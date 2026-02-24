<?php

namespace App\Jobs;

use App\Models\AuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WriteAuditLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function handle(): void
    {
        logger()->info('audit.job.start', [
            'resource_type' => $this->data['resource_type'] ?? $this->data['auditable_type'] ?? null,
            'resource_id' => $this->data['resource_id'] ?? $this->data['auditable_id'] ?? null,
            'action' => $this->data['action'] ?? null,
            'has_payload_before' => array_key_exists('payload_before', $this->data) || array_key_exists('payload_before', $this->data),
            'has_payload_after' => array_key_exists('payload_after', $this->data) || array_key_exists('new_values', $this->data),
        ]);

        // If this audit concerns ClienteEquipamento, log the incoming payloads for inspection
        if (stripos($this->data['resource_type'] ?? ($this->data['auditable_type'] ?? ''), 'ClienteEquipamento') !== false
            || stripos($this->data['resource_type'] ?? ($this->data['auditable_type'] ?? ''), 'cliente_equipamento') !== false) {
            logger()->info('audit.job.cliente_equipamento.payloads', [
                'payload_before' => $this->data['payload_before'] ?? $this->data['old_values'] ?? null,
                'payload_after' => $this->data['payload_after'] ?? $this->data['new_values'] ?? null,
                'raw' => $this->data,
            ]);
            // Try to load the current DB row, if resource_id present
            $id = $this->data['resource_id'] ?? $this->data['auditable_id'] ?? null;
            if (!empty($id)) {
                try {
                    $current = \App\Models\ClienteEquipamento::find($id);
                    logger()->info('audit.job.cliente_equipamento.current_row', [
                        'id' => $id,
                        'attributes' => $current?->getAttributes() ?? null,
                    ]);
                } catch (\Throwable $e) {
                    logger()->warning('audit.job.cliente_equipamento.load_failed', ['id' => $id, 'error' => $e->getMessage()]);
                }
            }
        }

        $key = config('app.audit_key') ?? env('AUDIT_HMAC_KEY');
        if (empty($key)) {
            // Fallback: write to logs and abort
            logger()->warning('AUDIT_HMAC_KEY not configured — skipping audit write.', $this->data);
            return;
        }

        logger()->info('audit.job.before_transaction', ['resource' => $this->data['resource_type'] ?? ($this->data['auditable_type'] ?? null), 'resource_id' => $this->data['resource_id'] ?? ($this->data['auditable_id'] ?? null)]);

        DB::transaction(function () use ($key) {
            // prefer chain_index if the column exists, otherwise fall back to id
            if (Schema::hasColumn('audit_logs', 'chain_index')) {
                $last = AuditLog::orderBy('chain_index', 'desc')->lockForUpdate()->first();
                $prevHash = $last?->hmac ?? '0';
                $chainIndex = ($last?->chain_index ?? 0) + 1;
            } else {
                $last = AuditLog::orderBy('id', 'desc')->lockForUpdate()->first();
                $prevHash = $last?->hmac ?? '0';
                $chainIndex = ($last?->id ?? 0) + 1; // best-effort chain index
            }

            $payloadToSign = [
                'prev' => $prevHash,
                'data' => $this->data,
                'ts' => now()->toISOString(),
            ];

            $toSign = json_encode($payloadToSign, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $hmac = hash_hmac('sha256', $toSign, $key);

            // Adaptive mapping: support different existing audit_logs schemas
            $attributes = [];

            // HMAC/chain columns (optional)
            if (Schema::hasColumn('audit_logs', 'hmac')) {
                $attributes['hmac'] = $hmac;
            }
            if (Schema::hasColumn('audit_logs', 'prev_hash')) {
                $attributes['prev_hash'] = $prevHash;
            }
            if (Schema::hasColumn('audit_logs', 'chain_index')) {
                $attributes['chain_index'] = $chainIndex;
            }

            // Common legacy-style columns
            if (Schema::hasColumn('audit_logs', 'user_id')) {
                $attributes['user_id'] = $this->data['actor_id'] ?? null;
            }
            if (Schema::hasColumn('audit_logs', 'role')) {
                $attributes['role'] = $this->data['actor_role'] ?? ($this->data['actor_name'] ?? null);
            }
            if (Schema::hasColumn('audit_logs', 'action')) {
                $attributes['action'] = $this->data['action'] ?? null;
            }
            if (Schema::hasColumn('audit_logs', 'auditable_type')) {
                $attributes['auditable_type'] = $this->data['resource_type'] ?? null;
            }
            if (Schema::hasColumn('audit_logs', 'auditable_id')) {
                $attributes['auditable_id'] = $this->data['resource_id'] ?? null;
            }
            if (Schema::hasColumn('audit_logs', 'old_values')) {
                $attributes['old_values'] = $this->data['payload_before'] ?? null;
            }
            if (Schema::hasColumn('audit_logs', 'new_values')) {
                $attributes['new_values'] = $this->data['payload_after'] ?? null;
            }
            if (Schema::hasColumn('audit_logs', 'ip_address')) {
                $attributes['ip_address'] = $this->data['ip'] ?? null;
            }
            if (Schema::hasColumn('audit_logs', 'user_agent')) {
                $attributes['user_agent'] = $this->data['user_agent'] ?? null;
            }
            if (Schema::hasColumn('audit_logs', 'url')) {
                $attributes['url'] = $this->data['meta']['url'] ?? null;
            }

            // Fallback to the newer naming if present
            if (Schema::hasColumn('audit_logs', 'actor_id')) {
                $attributes['actor_id'] = $this->data['actor_id'] ?? null;
            }
            if (Schema::hasColumn('audit_logs', 'actor_name')) {
                $attributes['actor_name'] = $this->data['actor_name'] ?? null;
            }
            if (Schema::hasColumn('audit_logs', 'actor_role')) {
                $attributes['actor_role'] = $this->data['actor_role'] ?? null;
            }
            if (Schema::hasColumn('audit_logs', 'module')) {
                $attributes['module'] = $this->data['module'] ?? null;
            }
            if (Schema::hasColumn('audit_logs', 'resource_type')) {
                $attributes['resource_type'] = $this->data['resource_type'] ?? null;
            }
            if (Schema::hasColumn('audit_logs', 'resource_id')) {
                $attributes['resource_id'] = $this->data['resource_id'] ?? null;
            }
            if (Schema::hasColumn('audit_logs', 'payload_before')) {
                $attributes['payload_before'] = $this->data['payload_before'] ?? null;
            }
            if (Schema::hasColumn('audit_logs', 'payload_after')) {
                $attributes['payload_after'] = $this->data['payload_after'] ?? null;
            }
            if (Schema::hasColumn('audit_logs', 'meta')) {
                $attributes['meta'] = $this->data['meta'] ?? null;
            }

            // created_at
            if (Schema::hasColumn('audit_logs', 'created_at')) {
                $attributes['created_at'] = now();
            }

            // Ensure arrays are JSON-encoded for DB drivers that expect strings
            foreach (['old_values','new_values','payload_before','payload_after','meta'] as $k) {
                if (array_key_exists($k, $attributes) && is_array($attributes[$k])) {
                    $attributes[$k] = json_encode($attributes[$k], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
            }

            logger()->info('audit.job.will_create_auditrow', ['attributes_preview' => array_intersect_key($attributes, array_flip(['actor_id','action','resource_type','resource_id','hmac','chain_index']))]);

            $created = AuditLog::create($attributes);

            logger()->info('audit.job.created', ['id' => $created?->id ?? null, 'chain_index' => $created?->chain_index ?? null, 'resource_type' => $attributes['resource_type'] ?? null, 'resource_id' => $attributes['resource_id'] ?? null]);
        });
    }
}
