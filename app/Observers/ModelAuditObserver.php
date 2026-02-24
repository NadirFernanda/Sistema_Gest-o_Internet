<?php

namespace App\Observers;

use App\Jobs\WriteAuditLogJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ModelAuditObserver
{
    protected function buildPayload($model, string $action, $channel = 'web') : array
    {
        $user = Auth::user();
        $actorId = $user ? $user->id : null;
        $actorName = $user ? ($user->name ?? $user->email) : null;
        $actorRole = $user && method_exists($user, 'getRoleNames') ? implode(',', $user->getRoleNames()->toArray()) : null;

        $before = $model->getOriginal();
        $after = $model->getAttributes();

        return [
            'actor_id' => $actorId,
            'actor_name' => $actorName,
            'actor_role' => $actorRole,
            'ip' => request()->ip() ?? null,
            'user_agent' => request()->userAgent() ?? null,
            'module' => class_basename($model),
            'resource_type' => get_class($model),
            'resource_id' => $model->getKey(),
            'action' => $action,
            'payload_before' => $before ?: null,
            'payload_after' => $after ?: null,
            'request_id' => request()->header('X-Request-Id') ?? (Str::uuid()->toString()),
            'channel' => $channel,
        ];
    }

    public function created($model): void
    {
        $this->pushAudit($this->buildPayload($model, 'created'));
    }

    public function updated($model): void
    {
        $this->pushAudit($this->buildPayload($model, 'updated'));
    }

    public function deleted($model): void
    {
        $this->pushAudit($this->buildPayload($model, 'deleted'));
    }

    protected function pushAudit(array $payload): void
    {
        // Dispatch the audit job after DB commit to avoid running it inside
        // the current transaction (which could cause the outer transaction
        // to rollback if the job throws). Using afterCommit ensures the job
        // is queued/executed only once the DB transaction succeeds.
        try {
            WriteAuditLogJob::dispatch($payload)->afterCommit();
        } catch (\Throwable $e) {
            logger()->error('audit.dispatch_failed', ['error' => $e->getMessage(), 'payload' => $payload]);
        }
    }
}
