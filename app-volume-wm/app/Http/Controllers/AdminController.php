<?php

namespace App\Http\Controllers;

use App\Models\ChromebookInventory;
use App\Models\ChromebookUsage;
use App\Services\ChromebookCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    private const DISPLAY_TIMEZONE = 'America/New_York';

    private ChromebookCacheService $cacheService;

    public function __construct(ChromebookCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Show the administration dashboard
     */
    public function index()
    {
        $stats = [
            'inventory_count' => ChromebookInventory::count(),
            'usage_count' => ChromebookUsage::count(),
            'latest_usage' => ChromebookUsage::latest('recorded_at')->first()?->recorded_at?->setTimezone(self::DISPLAY_TIMEZONE),
            'oldest_usage' => ChromebookUsage::oldest('recorded_at')->first()?->recorded_at?->setTimezone(self::DISPLAY_TIMEZONE),
        ];

        $jobs = [
            [
                'name' => 'Sync Inventory',
                'command' => 'chromebook:sync-inventory',
                'description' => 'Fetches all chromebooks from Google Admin and updates the inventory table',
                'schedule' => 'Daily at midnight EST',
                'cache_key' => 'job:sync-inventory:running',
                'last_ran' => Cache::get('job:sync-inventory:last_ran')?->setTimezone(self::DISPLAY_TIMEZONE),
            ],
            [
                'name' => 'Update Usage (ES)',
                'command' => 'chromebook:update-usage-es',
                'description' => 'Updates usage for Elementary School OUs (Devices/ES, Students/ES)',
                'schedule' => 'Every 3 hours (0:00, 3:00, ...)',
                'cache_key' => 'job:update-usage-es:running',
                'last_ran' => Cache::get('job:update-usage-es:last_ran')?->setTimezone(self::DISPLAY_TIMEZONE),
            ],
            [
                'name' => 'Update Usage (MS)',
                'command' => 'chromebook:update-usage-ms',
                'description' => 'Updates usage for Middle School OUs (Devices/MS, Students/MS)',
                'schedule' => 'Every 3 hours (1:00, 4:00, ...)',
                'cache_key' => 'job:update-usage-ms:running',
                'last_ran' => Cache::get('job:update-usage-ms:last_ran')?->setTimezone(self::DISPLAY_TIMEZONE),
            ],
            [
                'name' => 'Update Usage (HS)',
                'command' => 'chromebook:update-usage-hs',
                'description' => 'Updates usage for High School OUs (Devices/HS, Students/HS)',
                'schedule' => 'Every 3 hours (2:00, 5:00, ...)',
                'cache_key' => 'job:update-usage-hs:running',
                'last_ran' => Cache::get('job:update-usage-hs:last_ran')?->setTimezone(self::DISPLAY_TIMEZONE),
            ],
            [
                'name' => 'Cleanup Usage',
                'command' => 'chromebook:cleanup-usage',
                'description' => 'Removes usage records older than 1 month',
                'schedule' => 'Monthly on 1st at 1AM EST',
                'cache_key' => 'job:cleanup-usage:running',
                'last_ran' => Cache::get('job:cleanup-usage:last_ran')?->setTimezone(self::DISPLAY_TIMEZONE),
            ],
        ];

        return view('admin.index', compact('stats', 'jobs'));
    }

    /**
     * Get current stats via AJAX
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'inventory_count' => ChromebookInventory::count(),
            'usage_count' => ChromebookUsage::count(),
            'latest_usage' => ChromebookUsage::latest('recorded_at')->first()?->recorded_at?->setTimezone(self::DISPLAY_TIMEZONE)->diffForHumans(),
            'oldest_usage' => ChromebookUsage::oldest('recorded_at')->first()?->recorded_at?->setTimezone(self::DISPLAY_TIMEZONE)->diffForHumans(),
        ]);
    }

    /**
     * Run a specific cron job manually
     */
    public function runJob(Request $request): JsonResponse
    {
        $allowedCommands = [
            'chromebook:sync-inventory',
            'chromebook:update-usage-es',
            'chromebook:update-usage-ms',
            'chromebook:update-usage-hs',
            'chromebook:cleanup-usage',
        ];

        $validated = $request->validate([
            'command' => 'required|string|in:' . implode(',', $allowedCommands)
        ]);

        $command = $validated['command'];

        // Check if any job is already running
        $anyRunning = Cache::has('job:sync-inventory:running')
            || Cache::has('job:update-usage-es:running')
            || Cache::has('job:update-usage-ms:running')
            || Cache::has('job:update-usage-hs:running')
            || Cache::has('job:cleanup-usage:running');

        if ($anyRunning) {
            return response()->json([
                'success' => false,
                'message' => 'A job is already running. Please wait for it to complete.'
            ], 409);
        }

        // Run the command in background using nohup
        $artisanPath = base_path('artisan');
        $logPath = storage_path('logs/manual-job.log');
        $shellCommand = sprintf(
            'nohup php %s %s >> %s 2>&1 &',
            escapeshellarg($artisanPath),
            escapeshellarg($command),
            escapeshellarg($logPath)
        );

        exec($shellCommand);

        return response()->json([
            'success' => true,
            'message' => 'Job started successfully'
        ]);
    }

    /**
     * Check if a job is currently running
     */
    public function jobStatus(Request $request): JsonResponse
    {
        $jobs = [
            'sync-inventory',
            'update-usage-es',
            'update-usage-ms',
            'update-usage-hs',
            'cleanup-usage',
        ];

        $statuses = [];
        foreach ($jobs as $job) {
            $lastRan = Cache::get("job:{$job}:last_ran");
            $statuses[$job] = [
                'running' => Cache::has("job:{$job}:running"),
                'last_ran' => $lastRan ? $lastRan->setTimezone(self::DISPLAY_TIMEZONE)->format('M j, Y g:i A') : null,
                'last_ran_human' => $lastRan ? $lastRan->diffForHumans() : null,
            ];
        }

        return response()->json($statuses);
    }

    /**
     * Show the usage data management page
     */
    public function usage(Request $request)
    {
        // Sorting
        $sortBy = $request->input('sort_by', 'recorded_at');
        $sortDir = $request->input('sort_dir', 'desc');

        // Validate sort column
        $allowedSortColumns = ['serial_number', 'asset_id', 'user_email', 'recorded_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'recorded_at';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

        $query = ChromebookUsage::query()->orderBy($sortBy, $sortDir);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                  ->orWhere('asset_id', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%");
            });
        }

        // Date range filter
        if ($request->filled('from_date')) {
            $query->where('recorded_at', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->where('recorded_at', '<=', $request->input('to_date') . ' 23:59:59');
        }

        // Per page (max 5000)
        $perPage = min((int) $request->input('per_page', 25), 5000);
        if ($perPage < 1) {
            $perPage = 25;
        }

        $usageRecords = $query->paginate($perPage)->withQueryString();

        return view('admin.usage', compact('usageRecords'));
    }

    /**
     * Get usage records as JSON for AJAX
     */
    public function usageData(Request $request): JsonResponse
    {
        $query = ChromebookUsage::query()->orderBy('recorded_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                  ->orWhere('asset_id', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%");
            });
        }

        $records = $query->paginate(25);

        return response()->json($records);
    }

    /**
     * Update a usage record
     */
    public function updateUsageRecord(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'serial_number' => 'required|string|max:100',
            'asset_id' => 'nullable|string|max:100',
            'user_email' => 'required|email|max:255',
            'recorded_at' => 'required|date',
        ]);

        $record = ChromebookUsage::findOrFail($id);
        $record->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Record updated successfully',
            'record' => $record
        ]);
    }

    /**
     * Delete a usage record
     */
    public function deleteUsageRecord(int $id): JsonResponse
    {
        $record = ChromebookUsage::findOrFail($id);
        $record->delete();

        return response()->json([
            'success' => true,
            'message' => 'Record deleted successfully'
        ]);
    }

    /**
     * Create a new usage record
     */
    public function createUsageRecord(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'serial_number' => 'required|string|max:100',
            'asset_id' => 'nullable|string|max:100',
            'user_email' => 'required|email|max:255',
            'recorded_at' => 'required|date',
        ]);

        $record = ChromebookUsage::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Record created successfully',
            'record' => $record
        ]);
    }
}
