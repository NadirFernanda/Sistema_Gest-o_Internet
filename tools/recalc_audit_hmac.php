<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$row = DB::table('audit_logs')->where('id', 1)->first();
if (! $row) {
    echo "Row not found\n";
    exit(2);
}

$key = config('app.audit_key') ?? env('AUDIT_HMAC_KEY');

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

$ts = ($row->created_at ? \Carbon\Carbon::parse($row->created_at)->format(DATE_ATOM) : now()->format(DATE_ATOM));
$payloadToSign = ['prev' => ($row->prev_hash ?? '0'), 'data' => $data, 'ts' => $ts];
$toSign = json_encode($payloadToSign, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$hmac = hash_hmac('sha256', $toSign, $key);

echo "stored: " . ($row->hmac ?? '[null]') . "\n";
echo "calc:   " . $hmac . "\n";
echo "payload: \n" . $toSign . "\n";

return 0;
