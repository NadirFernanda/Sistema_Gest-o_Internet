<?php
/**
 * One-time script to find and remove duplicate reseller applications,
 * keeping only the most recent one per email, and set reseller_mode from internet_type.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ResellerApplication;
use Illuminate\Support\Facades\DB;

// 1. Find duplicates
$dupes = DB::select('SELECT email, COUNT(*) as cnt FROM reseller_applications GROUP BY email HAVING cnt > 1');
echo "Emails duplicados: " . count($dupes) . "\n";

foreach ($dupes as $dupe) {
    // Keep the newest (highest ID), delete the rest
    $all = ResellerApplication::where('email', $dupe->email)->orderByDesc('id')->get();
    $keep = $all->first();
    $toDelete = $all->slice(1);

    echo "  {$dupe->email}: manter #{$keep->id}, apagar " . $toDelete->pluck('id')->implode(', ') . "\n";

    ResellerApplication::whereIn('id', $toDelete->pluck('id')->toArray())->delete();
}

// 2. Fill reseller_mode from internet_type where null
$updated = ResellerApplication::whereNull('reseller_mode')
    ->whereNotNull('internet_type')
    ->update(['reseller_mode' => DB::raw('internet_type')]);

echo "\nreseller_mode preenchido em {$updated} registos.\n";
echo "Concluído.\n";
