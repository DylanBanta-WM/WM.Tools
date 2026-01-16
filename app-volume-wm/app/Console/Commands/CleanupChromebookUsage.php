<?php

namespace App\Console\Commands;

use App\Services\ChromebookCacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CleanupChromebookUsage extends Command
{
    protected $signature = 'chromebook:cleanup-usage';
    protected $description = 'Remove chromebook usage records older than 1 month';

    public function handle(ChromebookCacheService $cacheService): int
    {
        Cache::put('job:cleanup-usage:running', true, now()->addHours(1));

        try {
            $this->info('Starting chromebook usage cleanup...');

            $deleted = $cacheService->cleanupOldUsage();

            $this->info("Cleanup completed: {$deleted} records deleted");

            // Store last run timestamp (persist for 30 days)
            Cache::put('job:cleanup-usage:last_ran', now(), now()->addDays(30));

            return Command::SUCCESS;
        } finally {
            Cache::forget('job:cleanup-usage:running');
        }
    }
}
