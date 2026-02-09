<?php

namespace App\Console\Commands;

use App\Services\ChromebookCacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UpdateChromebookUsage extends Command
{
    protected $signature = 'chromebook:update-usage';
    protected $description = 'Update chromebook usage records from Google Admin (GAM)';

    public function handle(ChromebookCacheService $cacheService): int
    {
        Cache::put('job:update-usage:running', true, now()->addHours(6));

        try {
            $this->info('Starting chromebook usage update...');

            $stats = $cacheService->updateUsage();

            $this->info('Update completed:');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Checked', $stats['checked']],
                    ['New Records', $stats['created']],
                    ['Skipped (unchanged)', $stats['skipped']],
                    ['Failed', $stats['failed']],
                ]
            );

            // Store last run timestamp (persist for 30 days)
            Cache::put('job:update-usage:last_ran', now(), now()->addDays(30));

            return $stats['failed'] > ($stats['checked'] / 2) ? Command::FAILURE : Command::SUCCESS;
        } finally {
            Cache::forget('job:update-usage:running');
        }
    }
}
