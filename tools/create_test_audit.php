<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Jobs\WriteAuditLogJob;

WriteAuditLogJob::dispatchSync([
    'actor_id' => 1,
    'actor_name' => 'dev-script',
    'actor_role' => 'developer',
    'module' => 'Test',
    'action' => 'created',
    'resource_type' => 'App\\Models\\Cliente',
    'resource_id' => 999999,
    'payload_before' => null,
    'payload_after' => ['dummy' => 'value'],
    'meta' => ['source' => 'tools/create_test_audit.php'],
    'ip' => '127.0.0.1',
    'user_agent' => 'cli',
    'session_id' => null,
    'channel' => 'cli',
]);

echo "Test audit dispatched\n";
