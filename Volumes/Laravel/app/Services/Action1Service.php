<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class Action1Service
{
    /**
     * Action1 API base URL
     */
    private string $baseUrl = 'https://app.action1.com/api/3.0';

    /**
     * OAuth token endpoint
     */
    private string $tokenUrl = 'https://app.action1.com/api/3.0/oauth2/token';

    /**
     * Default timeout for API requests (in seconds)
     */
    private int $timeout = 30;

    /**
     * Cache key for access token
     */
    private string $tokenCacheKey = 'action1_access_token';

    /**
     * Check if Action1 credentials are configured
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty(config('services.action1.client_id')) &&
               !empty(config('services.action1.client_secret'));
    }

    /**
     * Get a valid access token, using cache when possible
     *
     * @return array Returns ['success' => bool, 'token' => string|null, 'error' => string|null]
     */
    public function getToken(): array
    {
        // Check if we have a cached token
        $cachedToken = Cache::get($this->tokenCacheKey);
        if ($cachedToken) {
            return [
                'success' => true,
                'token' => $cachedToken,
                'error' => null,
            ];
        }

        // Get new token using configured credentials
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'token' => null,
                'error' => 'Action1 API credentials not configured',
            ];
        }

        $result = $this->authenticate(
            config('services.action1.client_id'),
            config('services.action1.client_secret')
        );

        // Cache the token if successful (expire 5 minutes before actual expiry)
        if ($result['success'] && $result['token']) {
            $expiresIn = ($result['expires_in'] ?? 3600) - 300;
            Cache::put($this->tokenCacheKey, $result['token'], max(60, $expiresIn));
        }

        return $result;
    }

    /**
     * Authenticate and get OAuth access token
     *
     * @param string $clientId
     * @param string $clientSecret
     * @return array Returns ['success' => bool, 'token' => string|null, 'error' => string|null]
     */
    public function authenticate(string $clientId, string $clientSecret): array
    {
        try {
            $response = Http::asForm()
                ->timeout($this->timeout)
                ->post($this->tokenUrl, [
                    'grant_type' => 'client_credentials',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['access_token'] ?? null;

                if ($token) {
                    Log::info('Action1 OAuth token obtained successfully');
                    return [
                        'success' => true,
                        'token' => $token,
                        'expires_in' => $data['expires_in'] ?? 3600,
                        'error' => null,
                    ];
                }

                return [
                    'success' => false,
                    'token' => null,
                    'error' => 'No access token in response',
                ];
            }

            Log::warning('Action1 OAuth token request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'token' => null,
                'error' => $response->json()['error_description'] ?? 'Authentication failed',
            ];
        } catch (\Exception $e) {
            Log::error('Action1 OAuth error', ['exception' => $e->getMessage()]);

            return [
                'success' => false,
                'token' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get OAuth access token (legacy method for compatibility)
     *
     * @param string $clientId
     * @param string $clientSecret
     * @return array
     */
    public function getAccessToken(string $clientId, string $clientSecret): array
    {
        return $this->authenticate($clientId, $clientSecret);
    }

    /**
     * Make an authenticated API request
     *
     * @param string $method HTTP method (GET, POST, PATCH, DELETE)
     * @param string $endpoint API endpoint (e.g., '/organizations')
     * @param string $token OAuth access token
     * @param array $data Request data (for POST/PATCH)
     * @param array $query Query parameters (for GET)
     * @return array Returns ['success' => bool, 'data' => array|null, 'error' => string|null]
     */
    public function request(string $method, string $endpoint, string $token, array $data = [], array $query = []): array
    {
        try {
            $url = $this->baseUrl . $endpoint;

            $http = Http::withToken($token)
                ->timeout($this->timeout)
                ->acceptJson();

            $response = match (strtoupper($method)) {
                'GET' => $http->get($url, $query),
                'POST' => $http->post($url, $data),
                'PATCH' => $http->patch($url, $data),
                'DELETE' => $http->delete($url, $data),
                default => throw new \InvalidArgumentException("Unsupported HTTP method: $method"),
            };

            if ($response->successful()) {
                Log::info('Action1 API request successful', [
                    'method' => $method,
                    'endpoint' => $endpoint,
                ]);

                return [
                    'success' => true,
                    'data' => $response->json(),
                    'error' => null,
                    'status' => $response->status(),
                ];
            }

            Log::warning('Action1 API request failed', [
                'method' => $method,
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'data' => $response->json(),
                'error' => $response->json()['message'] ?? "Request failed with status {$response->status()}",
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('Action1 API error', [
                'method' => $method,
                'endpoint' => $endpoint,
                'exception' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'error' => $e->getMessage(),
                'status' => null,
            ];
        }
    }

    /**
     * List all organizations
     *
     * @param string $token
     * @return array
     */
    public function listOrganizations(string $token): array
    {
        return $this->request('GET', '/organizations', $token);
    }

    /**
     * List all reports
     *
     * @param string $token
     * @return array
     */
    public function listReports(string $token): array
    {
        return $this->request('GET', '/reports/all', $token);
    }

    /**
     * Get reports by category
     *
     * @param string $token
     * @param string $reportOrCategoryId
     * @return array
     */
    public function getReportsByCategory(string $token, string $reportOrCategoryId): array
    {
        return $this->request('GET', "/reports/all/{$reportOrCategoryId}", $token);
    }

    /**
     * Get report data
     *
     * @param string $token
     * @param string $orgId
     * @param string $reportId
     * @param array $query Optional query parameters (e.g., filters, pagination)
     * @return array
     */
    public function getReportData(string $token, string $orgId, string $reportId, array $query = []): array
    {
        return $this->request('GET', "/reportdata/{$orgId}/{$reportId}/data", $token, [], $query);
    }

    /**
     * Get report errors
     *
     * @param string $token
     * @param string $orgId
     * @param string $reportId
     * @return array
     */
    public function getReportErrors(string $token, string $orgId, string $reportId): array
    {
        return $this->request('GET', "/reportdata/{$orgId}/{$reportId}/errors", $token);
    }

    /**
     * Export a report
     *
     * @param string $token
     * @param string $orgId
     * @param string $reportId
     * @param array $query Export options
     * @return array
     */
    public function exportReport(string $token, string $orgId, string $reportId, array $query = []): array
    {
        return $this->request('GET', "/reportdata/{$orgId}/{$reportId}/export", $token, [], $query);
    }

    /**
     * Re-query a report
     *
     * @param string $token
     * @param string $orgId
     * @param string $reportId
     * @return array
     */
    public function requeryReport(string $token, string $orgId, string $reportId): array
    {
        return $this->request('POST', "/reportdata/{$orgId}/{$reportId}/requery", $token);
    }

    /**
     * List managed endpoints
     *
     * @param string $token
     * @param string $orgId
     * @param array $query Optional query parameters
     * @return array
     */
    public function listEndpoints(string $token, string $orgId, array $query = []): array
    {
        return $this->request('GET', "/endpoints/managed/{$orgId}", $token, [], $query);
    }

    /**
     * Get endpoint status
     *
     * @param string $token
     * @param string $orgId
     * @return array
     */
    public function getEndpointStatus(string $token, string $orgId): array
    {
        return $this->request('GET', "/endpoints/status/{$orgId}", $token);
    }

    /**
     * Search across reports, endpoints, and apps
     *
     * @param string $token
     * @param string $orgId
     * @param array $query Search query parameters
     * @return array
     */
    public function search(string $token, string $orgId, array $query = []): array
    {
        return $this->request('GET', "/search/{$orgId}", $token, [], $query);
    }
}
