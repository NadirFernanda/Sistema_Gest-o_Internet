<?php
// Standalone script — run with: php tools/seed_test_vouchers.php
// from the project root (where vendor/ and bootstrap/ are)

define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\WifiCode;
use Illuminate\Support\Facades\DB;

$target = 2000;
$plans  = ['diario', 'semanal', 'mensal'];
$added  = 0;

foreach ($plans as $plan) {
    $existing = WifiCode::where('plan_id', $plan)
        ->where('code', 'like', strtoupper($plan) . '-TEST-%')
        ->count();

    echo "[$plan] Encontrados: $existing / $target\n";

    DB::transaction(function () use ($plan, $target, $existing, &$added) {
        for ($i = $existing + 1; $i <= $target; $i++) {
            WifiCode::create([
                'code'    => strtoupper($plan) . '-TEST-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'plan_id' => $plan,
                'status'  => 'available',
            ]);
            $added++;
        }
    });

    $total = WifiCode::where('plan_id', $plan)
        ->where('code', 'like', strtoupper($plan) . '-TEST-%')
        ->where('status', 'available')
        ->count();
    echo "[$plan] Total disponivel agora: $total\n";
}

echo "\nTotal inseridos: $added\n";
echo "Concluido.\n";
