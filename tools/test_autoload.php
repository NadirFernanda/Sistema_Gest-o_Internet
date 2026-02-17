<?php
require __DIR__ . '/../vendor/autoload.php';

try {
    $c = new \App\Console\Commands\AuditBackfillCommand();
    echo get_class($c) . PHP_EOL;
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
    echo $e . PHP_EOL;
}
