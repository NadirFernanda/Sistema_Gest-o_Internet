<?php
// Simple WCAG contrast checker for key color pairs in clientes.css
$cssFile = __DIR__ . '/../public/css/clientes.css';
if (!file_exists($cssFile)) {
    echo "CSS file not found: $cssFile\n";
    exit(1);
}
$css = file_get_contents($cssFile);

function extractVar($css, $name) {
    if (preg_match('/--' . preg_quote($name, '/') . '\s*:\s*([^;\n]+)/', $css, $m)) {
        return trim($m[1]);
    }
    return null;
}

function hexToRgb($hex) {
    $hex = trim($hex);
    if (strpos($hex, '#') === 0) $hex = substr($hex,1);
    if (strlen($hex) == 3) {
        $r = hexdec(str_repeat($hex[0],2));
        $g = hexdec(str_repeat($hex[1],2));
        $b = hexdec(str_repeat($hex[2],2));
    } else {
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));
    }
    return [$r, $g, $b];
}

function sRGBtoLin($c) {
    $c = $c/255.0;
    if ($c <= 0.03928) return $c/12.92;
    return pow(($c+0.055)/1.055, 2.4);
}

function luminance($hex) {
    list($r,$g,$b) = hexToRgb($hex);
    $R = sRGBtoLin($r);
    $G = sRGBtoLin($g);
    $B = sRGBtoLin($b);
    return 0.2126*$R + 0.7152*$G + 0.0722*$B;
}

function contrastRatio($hex1, $hex2) {
    $L1 = luminance($hex1);
    $L2 = luminance($hex2);
    $light = max($L1,$L2);
    $dark = min($L1,$L2);
    return ($light + 0.05)/($dark + 0.05);
}

// Extract variables
$vars = ['yellow-500','yellow-600','gray-900','gray-700','gray-500','gray-200','gray-100','white'];
$colors = [];
foreach ($vars as $v) {
    $val = extractVar($css, $v);
    if ($val) $colors[$v] = $val;
}

// Normalize whites
if (!isset($colors['white'])) $colors['white'] = '#ffffff';

echo "Detected CSS variables:\n";
foreach ($colors as $k=>$v) echo " - $k: $v\n";
echo "\nRunning contrast checks for common UI pairs...\n\n";

$pairs = [
    ['white', 'yellow-500', 'button text (white on yellow)'],
    ['gray-900', 'white', 'body text on white'],
    ['gray-500', 'white', 'muted text on white'],
    ['gray-900', 'gray-100', 'text on input bg'],
    ['yellow-600', 'white', 'accent chip text on white'],
    ['gray-900', 'fff8e6', 'text on subtle yellow surface'],
];

foreach ($pairs as $p) {
    $a = $p[0]; $b = $p[1]; $label = $p[2];
    $hexA = isset($colors[$a]) ? $colors[$a] : $a;
    $hexB = isset($colors[$b]) ? $colors[$b] : $b;
    // clean possible rgba() or gradient values
    if (preg_match('/rgba?\(([^)]+)\)/', $hexA, $m)) {
        // skip complex
    }
    if (preg_match('/[^#0-9a-fA-F]/', $hexA)) {
        // try to extract last hex in string
        if (preg_match('/#([0-9a-fA-F]{6})/', $hexA, $m)) $hexA = '#'.$m[1];
    }
    if (preg_match('/[^#0-9a-fA-F]/', $hexB)) {
        if (preg_match('/#([0-9a-fA-F]{6})/', $hexB, $m)) $hexB = '#'.$m[1];
    }
    $hexA = trim($hexA, " \t\n\r\"");
    $hexB = trim($hexB, " \t\n\r\"");
    // ensure hex format
    if (strpos($hexA,'#')!==0) $hexA = '#'.$hexA;
    if (strpos($hexB,'#')!==0) $hexB = '#'.$hexB;
    $ratio = contrastRatio($hexA, $hexB);
    $meets = $ratio >= 4.5 ? 'AA (normal)' : ($ratio >= 3 ? 'AA Large' : 'Fail');
    printf("%-30s : %s on %s -> %.2f :1  [%s]\n", $label, $hexA, $hexB, $ratio, $meets);
}

echo "\nNotes:\n - 'AA (normal)' requires >= 4.5:1 for normal text.\n - 'AA Large' requires >= 3:1 for large text (>=18pt or 14pt bold).\n\n";

exit(0);
