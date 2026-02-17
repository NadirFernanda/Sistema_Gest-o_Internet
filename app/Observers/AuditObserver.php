<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    protected $sensitive = ['password','api_token','remember_token'];

    protected function scrub(array $data)
    {
        foreach ($this->sensitive as $k) {
            if (array_key_exists($k, $data)) {
                $data[$k] = '[REDACTED]';
            }
        }
        return $data;
    }

    public function created(Model $model)
    {
        $this->log('created', $model, null, $model->getAttributes());
    }

    public function updated(Model $model)
    {
        $this->log('updated', $model, $model->getOriginal(), $model->getAttributes());
    }

    public function deleted(Model $model)
    {
        $this->log('deleted', $model, $model->getOriginal(), null);
    }

    protected function log(string $action, Model $model, $old = null, $new = null)
    {
        $actor = app()->has('audit.actor') ? app('audit.actor') : null;

        $userId = $actor['id'] ?? (auth()->check() ? auth()->id() : null);
        $role = $actor['role'] ?? (auth()->check() ? (auth()->user()->role ?? null) : null);
        $now = now();

        $payload = [
            'user_id' => $userId,
            'role' => $role,
            'action' => $action,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'old_values' => $old ? $this->scrub((array) $old) : null,
            'new_values' => $new ? $this->scrub((array) $new) : null,
            'ip_address' => request()->ip() ?? null,
            'user_agent' => request()->userAgent() ?? null,
            'url' => request()->fullUrl() ?? null,
            'created_at' => $now->toDateTimeString(),
        ];

        $hmacKey = env('AUDIT_HMAC_KEY', config('app.key'));
        $hmacPayload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $payload['hmac'] = hash_hmac('sha256', $hmacPayload, $hmacKey);

        $audit = AuditLog::create($payload);

        // Dispatch replication to S3 (if configured) for immutability/archival
        if (class_exists('\App\Jobs\ReplicateAuditLog')) {
            try {
                \App\Jobs\ReplicateAuditLog::dispatch($audit);
            } catch (\Throwable $e) {
                // don't break main flow if job dispatch fails
            }
        }
    }
}
