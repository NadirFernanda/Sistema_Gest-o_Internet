<?php
// Dev tool: read OTP from active database sessions
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$sessions = \DB::table('sessions')->orderByDesc('last_activity')->take(20)->get();
$found = false;
foreach ($sessions as $s) {
    $payload = @unserialize(base64_decode($s->payload));
    if (!is_array($payload)) continue;
    if (isset($payload['reseller_otp_code'])) {
        echo "Session ID : " . $s->id . PHP_EOL;
        echo "OTP Code   : " . $payload['reseller_otp_code'] . PHP_EOL;
        echo "Email      : " . ($payload['reseller_otp_email'] ?? '?') . PHP_EOL;
        echo "Expires    : " . ($payload['reseller_otp_expires_at'] ?? '?') . PHP_EOL;
        echo PHP_EOL;
        $found = true;
    }
}
if (!$found) echo "Nenhuma sessão com OTP activo encontrada.\n";
