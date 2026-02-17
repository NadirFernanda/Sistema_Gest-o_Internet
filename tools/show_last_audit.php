<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$last = App\Models\AuditLog::orderBy('id','desc')->first();
if (! $last) {
    echo "No audit rows found\n";
    exit(1);
}

echo json_encode($last->toArray(), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) . PHP_EOL;
