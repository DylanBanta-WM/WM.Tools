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
}
