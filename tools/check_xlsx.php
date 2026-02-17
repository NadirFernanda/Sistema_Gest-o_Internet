<?php
require __DIR__ . '/../vendor/autoload.php';

$path = $argv[1] ?? null;
if (!$path) {
    echo "Usage: php check_xlsx.php path/to/file.xlsx\n";
    exit(2);
}

$full = __DIR__ . '/../' . $path;
if (!file_exists($full)) {
    echo "File not found: $full\n";
    exit(3);
}

try {
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($full);
    $reader->setReadDataOnly(true);
    $spreadsheet = $reader->load($full);
    $sheets = $spreadsheet->getAllSheets();
    echo "Loaded: $full\n";
    echo "Sheet count: " . count($sheets) . "\n";
    foreach ($sheets as $i => $sheet) {
        $title = $sheet->getTitle();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        echo sprintf("%d: %s — rows: %s, lastCol: %s\n", $i+1, $title, $highestRow, $highestColumn);
    }
    exit(0);
} catch (Exception $e) {
    echo "Error reading file: " . $e->getMessage() . "\n";
    exit(4);
}
