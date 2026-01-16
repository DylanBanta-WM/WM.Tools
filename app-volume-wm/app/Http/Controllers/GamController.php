<?php

namespace App\Http\Controllers;

use App\Services\GamService;
use App\Services\ChromebookCacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GamController extends Controller
{
    private GamService $gamService;
    private ChromebookCacheService $cacheService;

    public function __construct(GamService $gamService, ChromebookCacheService $cacheService)
    {
        $this->gamService = $gamService;
        $this->cacheService = $cacheService;
    }

    /**
     * Show the new student email creator interface
     */
    public function newStudent()
    {
        return view('gam.index');
    }

    /**
     * Check if an email exists in Google Workspace
     *
     * POST /api/gam/check-email
     * Body: { "email": "user@domain.com" }
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email'
        ]);

        $result = $this->gamService->getUserInfo($validated['email']);

        // If success is true, the user exists
        // If success is false and error contains "Does not exist", the user doesn't exist
        $exists = $result['success'];

        return response()->json([
            'exists' => $exists,
            'email' => $validated['email']
        ]);
    }

    /**
     * Show the Chromebook lookup interface
     */
    public function chromebookLookup()
    {
        return view('gam.chromebook-lookup');
    }

    /**
     * Look up recent users of a Chromebook by serial number (CACHED)
     *
     * POST /api/gam/chromebook-by-serial
     * Body: { "serial_number": "ABC123", "limit": 5 }
     */
    public function chromebookBySerial(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'serial_number' => 'required|string|max:100',
            'limit' => 'integer|min:1|max:10'
        ]);

        $limit = $validated['limit'] ?? 1;
        $results = $this->cacheService->findBySerial($validated['serial_number'], $limit);

        if ($results->isEmpty()) {
            return response()->json([
                'success' => false,
                'serial_number' => $validated['serial_number'],
                'message' => 'No cached data found for this serial number',
                'data' => []
            ]);
        }

        return response()->json([
            'success' => true,
            'serial_number' => $validated['serial_number'],
            'data' => $results->map(fn($r) => [
                'email' => $r->user_email,
                'asset_id' => $r->asset_id,
                'recorded_at' => $r->recorded_at->toIso8601String(),
                'recorded_at_human' => $r->recorded_at->diffForHumans(),
            ])
        ]);
    }

    /**
     * Look up Chromebooks recently used by a student email (CACHED)
     *
     * POST /api/gam/chromebook-by-user
     * Body: { "email": "student@domain.com", "limit": 5 }
     */
    public function chromebookByUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'limit' => 'integer|min:1|max:10'
        ]);

        $limit = $validated['limit'] ?? 1;
        $results = $this->cacheService->findByUser($validated['email'], $limit);

        if ($results->isEmpty()) {
            return response()->json([
                'success' => false,
                'email' => $validated['email'],
                'message' => 'No cached data found for this user',
                'data' => []
            ]);
        }

        return response()->json([
            'success' => true,
            'email' => $validated['email'],
            'data' => $results->map(fn($r) => [
                'serial_number' => $r->serial_number,
                'asset_id' => $r->asset_id,
                'recorded_at' => $r->recorded_at->toIso8601String(),
                'recorded_at_human' => $r->recorded_at->diffForHumans(),
            ])
        ]);
    }

    /**
     * Look up recent users of a Chromebook by asset ID (CACHED)
     *
     * POST /api/gam/chromebook-by-asset
     * Body: { "asset_id": "12345", "limit": 5 }
     */
    public function chromebookByAsset(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset_id' => 'required|string|max:100',
            'limit' => 'integer|min:1|max:10'
        ]);

        $limit = $validated['limit'] ?? 1;
        $results = $this->cacheService->findByAssetId($validated['asset_id'], $limit);

        if ($results->isEmpty()) {
            return response()->json([
                'success' => false,
                'asset_id' => $validated['asset_id'],
                'message' => 'No cached data found for this asset ID',
                'data' => []
            ]);
        }

        return response()->json([
            'success' => true,
            'asset_id' => $validated['asset_id'],
            'data' => $results->map(fn($r) => [
                'email' => $r->user_email,
                'serial_number' => $r->serial_number,
                'recorded_at' => $r->recorded_at->toIso8601String(),
                'recorded_at_human' => $r->recorded_at->diffForHumans(),
            ])
        ]);
    }
}
