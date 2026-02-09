<?php

namespace App\Http\Controllers;

use App\Services\Action1Service;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class Action1Controller extends Controller
{
    private Action1Service $action1Service;

    public function __construct(Action1Service $action1Service)
    {
        $this->action1Service = $action1Service;
    }

    /**
     * Show the Action1 API interface
     */
    public function index()
    {
        return view('action1.index', [
            'configured' => $this->action1Service->isConfigured(),
        ]);
    }

    /**
     * Get authentication status and token
     *
     * POST /api/action1/auth
     */
    public function authenticate(Request $request): JsonResponse
    {
        $result = $this->action1Service->getToken();

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'authenticated' => true,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error'],
        ], 401);
    }

    /**
     * Helper to get token or return error response
     */
    private function getTokenOrFail(): array|JsonResponse
    {
        $result = $this->action1Service->getToken();

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ], 401);
        }

        return $result;
    }

    /**
     * List organizations
     *
     * POST /api/action1/organizations
     */
    public function listOrganizations(Request $request): JsonResponse
    {
        $tokenResult = $this->getTokenOrFail();
        if ($tokenResult instanceof JsonResponse) {
            return $tokenResult;
        }

        $result = $this->action1Service->listOrganizations($tokenResult['token']);

        return response()->json([
            'success' => $result['success'],
            'data' => $result['data'],
            'message' => $result['error'],
        ], $result['success'] ? 200 : ($result['status'] ?? 500));
    }

    /**
     * List all reports
     *
     * POST /api/action1/reports
     */
    public function listReports(Request $request): JsonResponse
    {
        $tokenResult = $this->getTokenOrFail();
        if ($tokenResult instanceof JsonResponse) {
            return $tokenResult;
        }

        $result = $this->action1Service->listReports($tokenResult['token']);

        return response()->json([
            'success' => $result['success'],
            'data' => $result['data'],
            'message' => $result['error'],
        ], $result['success'] ? 200 : ($result['status'] ?? 500));
    }

    /**
     * Get reports by category
     *
     * POST /api/action1/reports/category
     * Body: { "category_id": "..." }
     */
    public function getReportsByCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|string',
        ]);

        $tokenResult = $this->getTokenOrFail();
        if ($tokenResult instanceof JsonResponse) {
            return $tokenResult;
        }

        $result = $this->action1Service->getReportsByCategory(
            $tokenResult['token'],
            $validated['category_id']
        );

        return response()->json([
            'success' => $result['success'],
            'data' => $result['data'],
            'message' => $result['error'],
        ], $result['success'] ? 200 : ($result['status'] ?? 500));
    }

    /**
     * Get report data
     *
     * POST /api/action1/reports/data
     * Body: { "org_id": "...", "report_id": "...", "query": {} }
     */
    public function getReportData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'org_id' => 'required|string',
            'report_id' => 'required|string',
            'query' => 'array',
        ]);

        $tokenResult = $this->getTokenOrFail();
        if ($tokenResult instanceof JsonResponse) {
            return $tokenResult;
        }

        $result = $this->action1Service->getReportData(
            $tokenResult['token'],
            $validated['org_id'],
            $validated['report_id'],
            $validated['query'] ?? []
        );

        return response()->json([
            'success' => $result['success'],
            'data' => $result['data'],
            'message' => $result['error'],
        ], $result['success'] ? 200 : ($result['status'] ?? 500));
    }

    /**
     * List endpoints
     *
     * POST /api/action1/endpoints
     * Body: { "org_id": "...", "query": {} }
     */
    public function listEndpoints(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'org_id' => 'required|string',
            'query' => 'array',
        ]);

        $tokenResult = $this->getTokenOrFail();
        if ($tokenResult instanceof JsonResponse) {
            return $tokenResult;
        }

        $result = $this->action1Service->listEndpoints(
            $tokenResult['token'],
            $validated['org_id'],
            $validated['query'] ?? []
        );

        return response()->json([
            'success' => $result['success'],
            'data' => $result['data'],
            'message' => $result['error'],
        ], $result['success'] ? 200 : ($result['status'] ?? 500));
    }

    /**
     * Get endpoint status
     *
     * POST /api/action1/endpoints/status
     * Body: { "org_id": "..." }
     */
    public function getEndpointStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'org_id' => 'required|string',
        ]);

        $tokenResult = $this->getTokenOrFail();
        if ($tokenResult instanceof JsonResponse) {
            return $tokenResult;
        }

        $result = $this->action1Service->getEndpointStatus(
            $tokenResult['token'],
            $validated['org_id']
        );

        return response()->json([
            'success' => $result['success'],
            'data' => $result['data'],
            'message' => $result['error'],
        ], $result['success'] ? 200 : ($result['status'] ?? 500));
    }

    /**
     * Search
     *
     * POST /api/action1/search
     * Body: { "org_id": "...", "query": {} }
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'org_id' => 'required|string',
            'query' => 'array',
        ]);

        $tokenResult = $this->getTokenOrFail();
        if ($tokenResult instanceof JsonResponse) {
            return $tokenResult;
        }

        $result = $this->action1Service->search(
            $tokenResult['token'],
            $validated['org_id'],
            $validated['query'] ?? []
        );

        return response()->json([
            'success' => $result['success'],
            'data' => $result['data'],
            'message' => $result['error'],
        ], $result['success'] ? 200 : ($result['status'] ?? 500));
    }

    /**
     * Re-query a report
     *
     * POST /api/action1/reports/requery
     * Body: { "org_id": "...", "report_id": "..." }
     */
    public function requeryReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'org_id' => 'required|string',
            'report_id' => 'required|string',
        ]);

        $tokenResult = $this->getTokenOrFail();
        if ($tokenResult instanceof JsonResponse) {
            return $tokenResult;
        }

        $result = $this->action1Service->requeryReport(
            $tokenResult['token'],
            $validated['org_id'],
            $validated['report_id']
        );

        return response()->json([
            'success' => $result['success'],
            'data' => $result['data'],
            'message' => $result['error'],
        ], $result['success'] ? 200 : ($result['status'] ?? 500));
    }

    /**
     * Generic API request - forwards any request to Action1 API
     *
     * POST /api/action1/request
     * Body: { "method": "GET|POST|PATCH|DELETE", "endpoint": "/...", "query": {}, "body": {} }
     */
    public function request(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'method' => 'required|string|in:GET,POST,PATCH,DELETE',
            'endpoint' => 'required|string',
            'query' => 'array',
            'body' => 'array',
        ]);

        $tokenResult = $this->getTokenOrFail();
        if ($tokenResult instanceof JsonResponse) {
            return $tokenResult;
        }

        $result = $this->action1Service->request(
            $validated['method'],
            $validated['endpoint'],
            $tokenResult['token'],
            $validated['body'] ?? [],
            $validated['query'] ?? []
        );

        return response()->json([
            'success' => $result['success'],
            'data' => $result['data'],
            'message' => $result['error'],
            'status' => $result['status'],
        ], $result['success'] ? 200 : ($result['status'] ?? 500));
    }
}
