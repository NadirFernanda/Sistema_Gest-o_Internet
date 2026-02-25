<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cliente;
use App\Models\EstoqueEquipamento;
use App\Models\ClienteEquipamento;

$c = Cliente::first();
$e = EstoqueEquipamento::first();
if (!$c || !$e) {
    echo "MISSING: cliente or estoque\n";
    exit(1);
}

$v = ClienteEquipamento::create([
    'cliente_id' => $c->id,
    'estoque_equipamento_id' => $e->id,
    'quantidade' => 1,
    'morada' => 'Teste',
    'ponto_referencia' => 'Ref',
    'forma_ligacao' => 'Fibra',
]);

if ($v && $v->id) {
    echo "CREATED: " . $v->id . "\n";
    exit(0);
}

echo "FAILED to create\n";
exit(2);
