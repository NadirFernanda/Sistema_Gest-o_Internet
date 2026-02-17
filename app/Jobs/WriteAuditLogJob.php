<?php

namespace App\Jobs;

use App\Models\AuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class WriteAuditLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function handle(): void
    {
        $secret = config('audit.secret', config('app.key'));

        DB::transaction(function () use ($secret) {
            // compute prev_hash from last row
            $prev = AuditLog::orderBy('id', 'desc')->limit(1)->first();
            $prevHash = $prev ? ($prev->hmac ?? null) : null;

            $data = $this->payload;
            $data['prev_hash'] = $prevHash;

            $body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $hmac = hash_hmac('sha256', $body, $secret);

            $data['hmac'] = $hmac;

            AuditLog::create([
                'actor_id' => $data['actor_id'] ?? null,
                'actor_name' => $data['actor_name'] ?? null,
                'actor_role' => $data['actor_role'] ?? null,
                'ip' => $data['ip'] ?? null,
                'user_agent' => $data['user_agent'] ?? null,
                'module' => $data['module'] ?? null,
                'resource_type' => $data['resource_type'] ?? null,
                'resource_id' => $data['resource_id'] ?? null,
                'action' => $data['action'] ?? null,
                'before' => $data['before'] ?? null,
                'after' => $data['after'] ?? null,
                'request_id' => $data['request_id'] ?? null,
                'channel' => $data['channel'] ?? null,
                'hmac' => $data['hmac'],
                'prev_hash' => $data['prev_hash'] ?? null,
            ]);
        });
    }
}
