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
        // If queue driver is sync or operator explicitly requests sync processing
        // via AUDIT_FORCE_SYNC, execute synchronously to avoid lost audits when
        // a worker isn't running. Otherwise dispatch to the queue normally.
        $forceSync = env('AUDIT_FORCE_SYNC', false);
        $queueDefault = config('queue.default');
        if ($forceSync || $queueDefault === 'sync') {
            WriteAuditLogJob::dispatchSync($payload);
            return;
        }

        WriteAuditLogJob::dispatch($payload);
    }
}
