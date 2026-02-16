<?php
// Usage: php tools/compare_csrf_session.php /tmp/login.html
// Compares the page CSRF token with the last DB session `_token`.

require __DIR__ . "/../vendor/autoload.php";
$app = require_once __DIR__ . "/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$loginHtml = $argv[1] ?? null;
if (! $loginHtml || ! file_exists($loginHtml)) {
    echo "Usage: php tools/compare_csrf_session.php /path/to/login.html\n";
    exit(1);
}

$html = file_get_contents($loginHtml);
$pageToken = null;
if (preg_match('/name="_token" value="([^"]+)"/', $html, $m)) {
    $pageToken = $m[1];
}

echo "PAGE_CSRF: " . ($pageToken ?? '(not found)') . "\n\n";

$s = DB::table('sessions')->orderBy('last_activity', 'desc')->first();
if (! $s) {
    echo "No sessions found in DB.\n";
    exit(0);
}

echo "SESSION ID: {$s->id}\n";
echo "LAST ACTIVITY: {$s->last_activity}\n";
echo "PAYLOAD LEN: " . strlen($s->payload) . "\n\n";

echo "PAYLOAD_RAW:\n" . $s->payload . "\n\n";

$found = null;
$arr = null;

// Try direct unserialize
try {
    $try = @unserialize($s->payload);
    if ($try !== false) {
        $arr = $try;
        echo "(unserialize) payload is PHP serialized array/object.\n";
    }
} catch (Throwable $e) {}

// Try base64 then unserialize
if ($arr === null) {
    try {
        $try2 = @unserialize(base64_decode($s->payload));
        if ($try2 !== false) {
            $arr = $try2;
            echo "(base64+unserialize) payload decoded.\n";
        }
    } catch (Throwable $e) {}
}

if (is_array($arr)) {
    if (isset($arr['_token'])) {
        $found = $arr['_token'];
    } else {
        // search nested arrays
        array_walk_recursive($arr, function ($v, $k) use (&$found) {
            if ($k === '_token' && $found === null) $found = $v;
        });
    }
}

echo "EXTRACTED_TOKEN: " . ($found ?? '(not found)') . "\n";

if ($pageToken && $found) {
    echo "COMPARE: " . ($pageToken === $found ? "MATCH" : "DIFFER") . "\n";
} else {
    echo "COMPARE: cannot compare (missing token).\n";
}

exit(0);
