<?php

namespace App\Http\Controllers;

use App\Services\GamService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GamController extends Controller
{
    private GamService $gamService;

    public function __construct(GamService $gamService)
    {
        $this->gamService = $gamService;
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
     * Look up recent users of a Chromebook by serial number
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
        $result = $this->gamService->getChromebookRecentUsers($validated['serial_number'], $limit);

        return response()->json([
            'success' => $result['success'],
            'serial_number' => $validated['serial_number'],
            'output' => $result['output'],
            'error' => $result['error']
        ]);
    }

    /**
     * Look up Chromebooks recently used by a student email
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
        $result = $this->gamService->getChromebooksByUser($validated['email'], $limit);

        return response()->json([
            'success' => $result['success'],
            'email' => $validated['email'],
            'output' => $result['output'],
            'error' => $result['error']
        ]);
    }
}
