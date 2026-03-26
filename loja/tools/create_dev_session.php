<?php
/**
 * Dev helper: creates a reseller session directly in the DB and returns the cookie value.
 * Usage: php tools/create_dev_session.php
 */
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$reseller = \DB::table('reseller_applications')
    ->where('email', 'agente@teste.ao')
    ->where('status', 'approved')
    ->first();

if (!$reseller) {
    echo "ERROR: No approved reseller found with email agente@teste.ao\n";
    exit(1);
}

// Create a proper Laravel session
$sessionId = \Illuminate\Support\Str::random(40);
$data = [
    'reseller_id' => $reseller->id,
    '_token'      => \Illuminate\Support\Str::random(40),
    '_flash'      => ['old' => [], 'new' => []],
];

$payload = base64_encode(serialize($data));

\DB::table('sessions')->insert([
    'id'            => $sessionId,
    'user_id'       => null,
    'ip_address'    => '127.0.0.1',
    'user_agent'    => 'Mozilla/5.0 (dev)',
    'payload'       => $payload,
    'last_activity' => time(),
]);

$cookieName = config('session.cookie');
echo "=== DEV SESSION CREATED ===\n";
echo "Reseller: {$reseller->full_name} <{$reseller->email}>\n";
echo "Cookie name : {$cookieName}\n";
echo "Cookie value: {$sessionId}\n";
echo "\nTo use in browser DevTools console:\n";
echo "document.cookie = \"{$cookieName}={$sessionId}; path=/\"\n";
echo "\nThen navigate to: http://127.0.0.1:8001/painel-revendedor\n";
