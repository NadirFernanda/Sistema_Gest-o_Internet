<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Usage: set AUDIT_HMAC_KEY in environment when running this script
$cmd = 'audit:backfill';
$opts = "--dry-run"; // default to dry-run
\Artisan::call($cmd . ' ' . $opts);
echo \Artisan::output();
