<?php
require __DIR__ . '/../vendor/autoload.php';
$dir = __DIR__ . '/../storage/app/relatorios';
$files = glob($dir . '/*');
if (empty($files)) {
    echo "Nenhum arquivo encontrado em $dir\n";
    exit(1);
}
usort($files, function($a, $b) { return filemtime($b) - filemtime($a); });
$f = $files[0];
echo "FILE: " . basename($f) . PHP_EOL;
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($f);
foreach ($spreadsheet->getSheetNames() as $s) {
    echo $s . PHP_EOL;
}
