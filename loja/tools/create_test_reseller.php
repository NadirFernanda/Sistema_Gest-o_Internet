<?php
// Run with: php artisan tinker --execute="require base_path('tools/create_test_reseller.php');"

use App\Models\ResellerApplication;
use App\Models\WifiCode;

$r = ResellerApplication::updateOrCreate(
    ['email' => 'agente@teste.ao'],
    [
        'full_name'             => 'Agente Teste AngolaWiFi',
        'document_number'       => '123456789LA041',
        'address'               => 'Rua dos Testes, 42, Luanda',
        'phone'                 => '923456789',
        'installation_location' => 'Luanda',
        'internet_type'         => 'own',
        'reseller_mode'         => 'own',
        'status'                => 'approved',
        'installation_fee_aoa'  => 100000,
        'bonus_vouchers_aoa'    => 50000,
        'monthly_target_aoa'    => 50000,
        'saldo_bonus_aoa'       => 5000,
        'maintenance_status'    => 'ok',
        'subject'               => 'Candidatura de teste',
        'message'               => 'Agente criado automaticamente para testes locais.',
    ]
);

$added = 0;
foreach (['diario' => 30, 'semanal' => 20, 'mensal' => 10] as $plan => $qty) {
    for ($i = 1; $i <= $qty; $i++) {
        WifiCode::firstOrCreate(
            ['code' => strtoupper($plan) . '-TEST-' . str_pad($i, 4, '0', STR_PAD_LEFT)],
            ['plan_id' => $plan, 'status' => 'available']
        );
        $added++;
    }
}

echo "\n";
echo "==============================================\n";
echo "  REVENDEDOR DE TESTE CRIADO COM SUCESSO\n";
echo "==============================================\n";
echo "  Nome    : " . $r->full_name . "\n";
echo "  Email   : " . $r->email . "\n";
echo "  ID      : " . $r->id . "\n";
echo "  Modo    : " . $r->reseller_mode . " (desconto 70%)\n";
echo "  Status  : " . $r->status . "\n";
echo "----------------------------------------------\n";
echo "  Stock adicionado:\n";
echo "    Diario  : 30 vouchers (DIARIO-TEST-0001..0030)\n";
echo "    Semanal : 20 vouchers (SEMANAL-TEST-0001..0020)\n";
echo "    Mensal  : 10 vouchers (MENSAL-TEST-0001..0010)\n";
echo "==============================================\n";
echo "  Login em: http://127.0.0.1:8001/painel-revendedor\n";
echo "  OTP vai para: storage/logs/laravel.log\n";
echo "==============================================\n\n";
