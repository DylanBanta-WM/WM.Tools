<?php

namespace App\Console\Commands;

use App\Services\ChromebookCacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncChromebookInventory extends Command
{
    protected $signature = 'chromebook:sync-inventory';
    protected $description = 'Sync chromebook inventory from Google Admin (GAM)';

    public function handle(ChromebookCacheService $cacheService): int
    {
        Cache::put('job:sync-inventory:running', true, now()->addHours(1));

        try {
            $this->info('Starting chromebook inventory sync...');

            $stats = $cacheService->syncInventory();

            $this->info('Sync completed:');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total Processed', $stats['total']],
                    ['Updated/Created', $stats['updated']],
                    ['Failed', $stats['failed']],
                ]
            );

            // Store last run timestamp (persist for 30 days)
            Cache::put('job:sync-inventory:last_ran', now(), now()->addDays(30));

            return $stats['failed'] > 0 ? Command::FAILURE : Command::SUCCESS;
        } finally {
            Cache::forget('job:sync-inventory:running');
        }
    }
}
