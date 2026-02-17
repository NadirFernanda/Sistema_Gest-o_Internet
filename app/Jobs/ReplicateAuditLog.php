<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\AuditLog;

class ReplicateAuditLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $auditLog;

    public function __construct(AuditLog $auditLog)
    {
        $this->auditLog = $auditLog;
        $this->onQueue('audit');
    }

    public function handle()
    {
        $log = $this->auditLog->toArray();
        $payload = json_encode($log, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Prefer configured s3 disk; fall back to local storage/app/audit_backups
        $disk = config('filesystems.audit_disk', env('AUDIT_DISK', 's3'));

        $path = 'audit_logs/'.date('Y/m/d')."/{$this->auditLog->id}.json";

        try {
            if (Storage::disk($disk)->put($path, $payload)) {
                // optionally set visibility
                try { Storage::disk($disk)->setVisibility($path, 'private'); } catch (\Throwable $e) {}
            }
        } catch (\Throwable $e) {
            // fallback to local
            Storage::disk('local')->put('audit_backups/'.$this->auditLog->id.'.json', $payload);
        }
    }
}
