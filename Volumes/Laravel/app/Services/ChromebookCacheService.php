<?php

namespace App\Services;

use App\Models\ChromebookInventory;
use App\Models\ChromebookUsage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ChromebookCacheService
{
    private GamService $gamService;

    public function __construct(GamService $gamService)
    {
        $this->gamService = $gamService;
    }

    /**
     * Sync chromebook inventory from GAM
     * Called daily at midnight
     *
     * @return array Statistics about the sync
     */
    public function syncInventory(): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'failed' => 0, 'total' => 0];

        $result = $this->gamService->getAllChromebooks();

        if (!$result['success']) {
            Log::error('Failed to fetch chromebook inventory from GAM', [
                'error' => $result['error']
            ]);
            return $stats;
        }

        $lines = explode("\n", trim($result['output']));

        if (count($lines) < 2) {
            Log::warning('No chromebook data returned from GAM');
            return $stats;
        }

        // Parse CSV header
        $headers = str_getcsv($lines[0]);
        $serialIndex = array_search('serialNumber', $headers);
        $assetIndex = array_search('annotatedAssetId', $headers);

        if ($serialIndex === false) {
            Log::error('Serial number column not found in GAM output');
            return $stats;
        }

        // Process each chromebook
        for ($i = 1; $i < count($lines); $i++) {
            if (empty(trim($lines[$i]))) {
                continue;
            }

            $values = str_getcsv($lines[$i]);
            $serialNumber = $values[$serialIndex] ?? null;
            $assetId = $assetIndex !== false ? ($values[$assetIndex] ?? null) : null;

            if (empty($serialNumber)) {
                continue;
            }

            $stats['total']++;

            try {
                ChromebookInventory::updateOrCreate(
                    ['serial_number' => $serialNumber],
                    ['asset_id' => $assetId]
                );
                $stats['updated']++;
            } catch (\Exception $e) {
                $stats['failed']++;
                Log::error('Failed to sync chromebook', [
                    'serial' => $serialNumber,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Chromebook inventory sync completed', $stats);
        return $stats;
    }

    /**
     * Update usage records for all chromebooks
     * Called every 15 minutes
     *
     * @return array Statistics about the update
     */
    public function updateUsage(): array
    {
        $stats = ['checked' => 0, 'created' => 0, 'skipped' => 0, 'failed' => 0];

        $chromebooks = ChromebookInventory::all();

        foreach ($chromebooks as $chromebook) {
            $this->processChromebookUsage($chromebook->serial_number, $chromebook->asset_id, $stats);
        }

        Log::info('Chromebook usage update completed', $stats);
        return $stats;
    }

    /**
     * Update usage records for chromebooks in specific OUs
     *
     * @param array $ous Array of OU paths to process
     * @param string $label Label for logging (e.g., 'ES', 'MS', 'HS')
     * @return array Statistics about the update
     */
    public function updateUsageByOUs(array $ous, string $label = ''): array
    {
        $stats = ['checked' => 0, 'created' => 0, 'skipped' => 0, 'failed' => 0];

        // Get chromebooks from the specified OUs
        $result = $this->gamService->getChromebooksByOUs($ous);

        if (!$result['success']) {
            Log::error("Failed to fetch chromebooks from OUs for {$label}", [
                'ous' => $ous,
                'error' => $result['error']
            ]);
            return $stats;
        }

        $lines = explode("\n", trim($result['output']));

        if (count($lines) < 2) {
            Log::warning("No chromebook data returned from OUs for {$label}", ['ous' => $ous]);
            return $stats;
        }

        // Parse CSV header
        $headers = str_getcsv($lines[0]);
        $serialIndex = array_search('serialNumber', $headers);
        $assetIndex = array_search('annotatedAssetId', $headers);

        if ($serialIndex === false) {
            Log::error("Serial number column not found in GAM output for {$label}");
            return $stats;
        }

        // Process each chromebook
        for ($i = 1; $i < count($lines); $i++) {
            if (empty(trim($lines[$i]))) {
                continue;
            }

            $values = str_getcsv($lines[$i]);
            $serialNumber = $values[$serialIndex] ?? null;
            $assetId = $assetIndex !== false ? ($values[$assetIndex] ?? null) : null;

            if (empty($serialNumber)) {
                continue;
            }

            $this->processChromebookUsage($serialNumber, $assetId, $stats);
        }

        Log::info("Chromebook usage update completed for {$label}", array_merge($stats, ['ous' => $ous]));
        return $stats;
    }

    /**
     * Process usage for a single chromebook
     *
     * @param string $serialNumber
     * @param string|null $assetId
     * @param array &$stats Statistics array (passed by reference)
     */
    private function processChromebookUsage(string $serialNumber, ?string $assetId, array &$stats): void
    {
        $stats['checked']++;

        try {
            $result = $this->gamService->getChromebookLastUser($serialNumber);

            if (!$result['success']) {
                $stats['failed']++;
                return;
            }

            $email = $this->parseRecentUserEmail($result['output']);

            if (empty($email)) {
                $stats['skipped']++;
                return;
            }

            // Check if we need to create a new record
            $latestUsage = ChromebookUsage::where('serial_number', $serialNumber)
                ->latest('recorded_at')
                ->first();

            // Only create if no record exists OR email differs
            if (!$latestUsage || $latestUsage->user_email !== $email) {
                ChromebookUsage::create([
                    'serial_number' => $serialNumber,
                    'asset_id' => $assetId,
                    'user_email' => $email,
                    'recorded_at' => now(),
                ]);
                $stats['created']++;
            } else {
                $stats['skipped']++;
            }

        } catch (\Exception $e) {
            $stats['failed']++;
            Log::error('Failed to update chromebook usage', [
                'serial' => $serialNumber,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Cleanup old usage records
     * Called monthly on 1st at 1AM
     *
     * @return int Number of records deleted
     */
    public function cleanupOldUsage(): int
    {
        // "Older than 1 month" = before the 1st of previous month
        $cutoffDate = Carbon::now()
            ->subMonth()
            ->startOfMonth();

        $deleted = ChromebookUsage::where('recorded_at', '<', $cutoffDate)->delete();

        Log::info('Chromebook usage cleanup completed', [
            'deleted' => $deleted,
            'cutoff_date' => $cutoffDate->toDateTimeString()
        ]);

        return $deleted;
    }

    /**
     * Find usage by serial number (for lookup page)
     *
     * @param string $serialNumber
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findBySerial(string $serialNumber, int $limit = 10)
    {
        return ChromebookUsage::where('serial_number', $serialNumber)
            ->orderBy('recorded_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Find usage by user email (for lookup page)
     *
     * @param string $email
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByUser(string $email, int $limit = 10)
    {
        return ChromebookUsage::where('user_email', 'like', "%{$email}%")
            ->orderBy('recorded_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Find usage by asset ID (for lookup page)
     *
     * @param string $assetId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByAssetId(string $assetId, int $limit = 10)
    {
        return ChromebookUsage::where('asset_id', $assetId)
            ->orderBy('recorded_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Parse email from GAM recentUsers output
     */
    private function parseRecentUserEmail(string $output): ?string
    {
        $lines = explode("\n", $output);

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (str_starts_with($trimmed, 'email:')) {
                return trim(str_replace('email:', '', $trimmed));
            }
        }

        return null;
    }
}
