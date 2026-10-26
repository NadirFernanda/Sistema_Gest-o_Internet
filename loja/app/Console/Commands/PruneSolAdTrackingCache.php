<?php

namespace App\Console\Commands;

use Illuminate\Cache\DatabaseStore;
use Illuminate\Cache\FileStore;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PruneSolAdTrackingCache extends Command
{
    protected $signature = 'ads:prune-sol-tracking-cache';

    protected $description = 'Remove expired SOL advertising tracking keys from the database cache.';

    public function handle(): int
    {
        $store = Cache::store('sol_ads')->getStore();
        if ($store instanceof FileStore) {
            $deleted = $this->pruneExpiredFileKeys($store);
            $this->info("Pruned {$deleted} expired SOL advertising tracking files.");

            return self::SUCCESS;
        }

        if (! $store instanceof DatabaseStore) {
            $this->info('The SOL tracking cache store handles expiration natively; no pruning was needed.');

            return self::SUCCESS;
        }

        $prefix = $store->getPrefix() . 'sol_ad_';
        $table = config('cache.stores.sol_ads.table', 'cache');
        $connection = config('cache.stores.sol_ads.connection');
        $deleted = DB::connection($connection)
            ->table($table)
            ->whereRaw('SUBSTRING(key, 1, ?) = ?', [strlen($prefix), $prefix])
            ->where('expiration', '<=', now()->timestamp)
            ->delete();

        $this->info("Pruned {$deleted} expired SOL advertising tracking keys.");

        return self::SUCCESS;
    }

    private function pruneExpiredFileKeys(FileStore $store): int
    {
        $filesystem = $store->getFilesystem();
        $directory = $store->getDirectory();
        if (! $filesystem->isDirectory($directory)) {
            return 0;
        }

        $now = now()->timestamp;
        $deleted = 0;

        foreach ($filesystem->allFiles($directory) as $file) {
            $path = $file->getPathname();
            try {
                $contents = $filesystem->get($path);
            } catch (FileNotFoundException) {
                continue;
            }

            $expiration = substr($contents, 0, 10);

            if (ctype_digit($expiration) && (int) $expiration <= $now) {
                $filesystem->delete($path);
                $deleted++;
            }
        }

        return $deleted;
    }
}
