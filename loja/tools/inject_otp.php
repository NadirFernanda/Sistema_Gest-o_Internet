<?php
// Dev tool: inject a fresh OTP into all active reseller sessions
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$otp     = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expires = now()->addMinutes(10)->toISOString();

$sessions = \DB::table('sessions')->get();
$updated  = 0;

foreach ($sessions as $s) {
    $payload = @unserialize(base64_decode($s->payload));
    if (!is_array($payload) || !isset($payload['reseller_otp_email'])) continue;

    $payload['reseller_otp_code']       = $otp;
    $payload['reseller_otp_expires_at'] = $expires;

    \DB::table('sessions')->where('id', $s->id)->update([
        'payload' => base64_encode(serialize($payload)),
    ]);
    $updated++;
    echo 'Email    : ' . $payload['reseller_otp_email'] . PHP_EOL;
}

echo 'OTP      : ' . $otp . PHP_EOL;
echo 'Válido até: ' . $expires . PHP_EOL;
echo 'Sessões  : ' . $updated . PHP_EOL;

if ($updated === 0) {
    echo PHP_EOL . 'Nenhuma sessão OTP activa encontrada.' . PHP_EOL;
    echo 'Abra o browser, vá a http://127.0.0.1:8000/painel-revendedor,' . PHP_EOL;
    echo 'introduza o email e clique "Enviar código". Depois corra este script.' . PHP_EOL;
}
