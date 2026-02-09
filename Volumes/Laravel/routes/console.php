<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Chromebook Caching Schedule

// Daily at midnight EST (5 AM UTC)
Schedule::command('chromebook:sync-inventory')
    ->dailyAt('05:00')
    ->timezone('America/New_York')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/chromebook-sync.log'));

// Elementary School - every 3 hours starting at midnight (0:00, 3:00, 6:00, 9:00, 12:00, 15:00, 18:00, 21:00)
Schedule::command('chromebook:update-usage-es')
    ->cron('0 0,3,6,9,12,15,18,21 * * *')
    ->timezone('America/New_York')
    ->withoutOverlapping(180)  // Lock for 3 hours max
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/chromebook-usage-es.log'));

// Middle School - every 3 hours starting at 1:00 (1:00, 4:00, 7:00, 10:00, 13:00, 16:00, 19:00, 22:00)
Schedule::command('chromebook:update-usage-ms')
    ->cron('0 1,4,7,10,13,16,19,22 * * *')
    ->timezone('America/New_York')
    ->withoutOverlapping(180)  // Lock for 3 hours max
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/chromebook-usage-ms.log'));

// High School - every 3 hours starting at 2:00 (2:00, 5:00, 8:00, 11:00, 14:00, 17:00, 20:00, 23:00)
Schedule::command('chromebook:update-usage-hs')
    ->cron('0 2,5,8,11,14,17,20,23 * * *')
    ->timezone('America/New_York')
    ->withoutOverlapping(180)  // Lock for 3 hours max
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/chromebook-usage-hs.log'));

// Monthly on 1st at 1 AM EST (6 AM UTC)
Schedule::command('chromebook:cleanup-usage')
    ->monthlyOn(1, '06:00')
    ->timezone('America/New_York')
    ->appendOutputTo(storage_path('logs/chromebook-cleanup.log'));
