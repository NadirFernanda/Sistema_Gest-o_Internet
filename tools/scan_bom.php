<?php
$cwd = realpath(__DIR__ . '/../');
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cwd));
foreach ($it as $f) {
    if (! $f->isFile()) continue;
    $name = $f->getPathname();
    if (substr($name, -4) !== '.php') continue;
    $h = @file_get_contents($name, false, null, 0, 3);
    if ($h === "\xEF\xBB\xBF") echo "BOM: $name\n";
}
