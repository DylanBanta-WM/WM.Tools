<?php

namespace App\Console\Commands;

use App\Services\ChromebookCacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UpdateChromebookUsageES extends Command
{
    protected $signature = 'chromebook:update-usage-es';
    protected $description = 'Update chromebook usage records for Elementary School OUs';

    private const OUS = [
        '/Devices/ES',
        '/Students/ES',
    ];

    public function handle(ChromebookCacheService $cacheService): int
    {
        Cache::put('job:update-usage-es:running', true, now()->addHours(6));

        try {
            $this->info('Starting chromebook usage update for Elementary School...');
            $this->info('OUs: ' . implode(', ', self::OUS));

            $stats = $cacheService->updateUsageByOUs(self::OUS, 'ES');

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
            Cache::put('job:update-usage-es:last_ran', now(), now()->addDays(30));

            return $stats['failed'] > ($stats['checked'] / 2) ? Command::FAILURE : Command::SUCCESS;
        } finally {
            Cache::forget('job:update-usage-es:running');
        }
    }
}
