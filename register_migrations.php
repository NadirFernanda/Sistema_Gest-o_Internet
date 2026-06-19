<?php
// Registro manual de migrações pendentes na base de dados
$db_host = '127.0.0.1';
$db_port = 5432;
$db_name = 'sga_mrtexas';
$db_user = 'postgres';
$db_pass = 'Fernanda';

try {
    $pdo = new PDO(
        "pgsql:host=$db_host;port=$db_port;dbname=$db_name",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Registrar migrações que já existem mas não estão no histórico
    $migrations = [
        ['migration' => '2026_05_22_120612_create_mikrotik_sites_table', 'batch' => 23],
        ['migration' => '2026_06_04_000003_drop_tickets_tables', 'batch' => 23],
    ];

    foreach ($migrations as $mig) {
        $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (:migration, :batch) ON CONFLICT DO NOTHING");
        $stmt->execute($mig);
    }

    echo "✅ Migrações registadas com sucesso\n";
    exit(0);
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
