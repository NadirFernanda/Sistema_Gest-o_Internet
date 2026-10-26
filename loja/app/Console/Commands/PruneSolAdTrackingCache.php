<?php

namespace App\Console\Commands;

use Illuminate\Cache\DatabaseStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PruneSolAdTrackingCache extends Command
{
    protected $signature = 'ads:prune-sol-tracking-cache';

    protected $description = 'Remove expired SOL advertising tracking keys from the database cache.';

    public function handle(): int
    {
        if (config('cache.default') !== 'database') {
            $this->info('Database cache is not the default store; no tracking cache rows need pruning.');

            return self::SUCCESS;
        }

        $store = Cache::store('database')->getStore();
        if (! $store instanceof DatabaseStore) {
            $this->error('The configured database cache store is not a Laravel DatabaseStore.');

            return self::FAILURE;
        }

        $prefix = $store->getPrefix() . 'sol_ad_';
        $table = config('cache.stores.database.table', 'cache');
        $connection = config('cache.stores.database.connection');
        $deleted = DB::connection($connection)
            ->table($table)
            ->whereRaw('SUBSTRING(key, 1, ?) = ?', [strlen($prefix), $prefix])
            ->where('expiration', '<=', now()->timestamp)
            ->delete();

        $this->info("Pruned {$deleted} expired SOL advertising tracking keys.");

        return self::SUCCESS;
    }
}
