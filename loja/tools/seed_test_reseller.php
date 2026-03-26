<?php
// Quick script to seed a test reseller (run from loja/ root: php tools/seed_test_reseller.php)
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ResellerApplication;

$email = 'agente@teste.ao';
$existing = ResellerApplication::where('email', $email)->first();

if ($existing) {
    echo "Already exists — id={$existing->id}  status={$existing->status}  mode={$existing->reseller_mode}  maintenance_paid_year={$existing->maintenance_paid_year}\n";
} else {
    $r = ResellerApplication::create([
        'full_name'             => 'Agente Teste',
        'document_number'      => '123456789LA123',
        'address'              => 'Luanda, Angola',
        'email'                => $email,
        'phone'                => '923000000',
        'installation_location'=> 'Luanda',
        'subject'              => 'Pedido de adesão',
        'message'              => 'Pedido de teste local',
        'status'               => 'approved',
        'reseller_mode'        => 'angolawifi',
        'internet_type'        => 'angolawifi',
        'installation_fee_aoa' => 0,
        'bonus_vouchers_aoa'   => 0,
        'monthly_target_aoa'   => 50000,
        'maintenance_paid_year'=> null,   // due → shows "Pagar manutenção agora"
        'maintenance_status'   => 'ok',
    ]);
    echo "Created — id={$r->id}  email={$r->email}  mode={$r->reseller_mode}\n";
}
