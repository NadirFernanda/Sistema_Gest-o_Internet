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
            'before' => $before ?: null,
            'after' => $after ?: null,
            'request_id' => request()->header('X-Request-Id') ?? (Str::uuid()->toString()),
            'channel' => $channel,
        ];
    }

    public function created($model): void
    {
        WriteAuditLogJob::dispatch($this->buildPayload($model, 'create'));
    }

    public function updated($model): void
    {
        WriteAuditLogJob::dispatch($this->buildPayload($model, 'update'));
    }

    public function deleted($model): void
    {
        WriteAuditLogJob::dispatch($this->buildPayload($model, 'delete'));
    }
}
