<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MocoService
{
    protected Client $client;
    protected string $apiKey;
    protected string $domain;

    public function __construct()
    {
        // Read configuration and validate early to provide helpful errors
        // Read from config; fall back to env to survive stale config cache
        $this->apiKey = (string) (config('services.moco.api_key') ?? env('MOCO_API_KEY') ?? '');
        $this->domain = (string) (config('services.moco.domain') ?? env('MOCO_DOMAIN') ?? '');
        $baseUrl = (string) (config('services.moco.base_url') ?? env('MOCO_BASE_URL') ?? '');

        if ($this->apiKey === '') {
            throw new RuntimeException('MOCO_API_KEY is not set. Please add MOCO_API_KEY to your .env file.');
        }

        if ($baseUrl === '') {
            throw new RuntimeException('MOCO_BASE_URL is not set. Please add MOCO_BASE_URL to your .env file.');
        }

        // Normalize base URL and domain
        $baseUrl = rtrim($baseUrl, '/');

        // If a tenant URL like https://{domain}.mocoapp.com/api/v1 is supplied,
        // extract the domain and switch to the canonical API host
        $parsed = parse_url($baseUrl);
        if (!empty($parsed['host']) && str_ends_with($parsed['host'], '.mocoapp.com')) {
            // Derive domain from subdomain part if not explicitly set (leave host as provided)
            $parts = explode('.', $parsed['host']);
            if (count($parts) >= 3) {
                $derivedDomain = $parts[0];
                if ($this->domain === '' && $derivedDomain !== 'api') {
                    $this->domain = $derivedDomain;
                }
            }
        }

        // Ensure trailing slash so relative paths resolve as <base>/<endpoint>
        $baseUrl = rtrim($baseUrl, '/') . '/';

        $headers = [
            'Authorization' => 'Token token=' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'Laravel-MOCO-Integration',
        ];

        // Optional: Firmen-Domain mitschicken, wenn gesetzt (z.B. "enodiasoftware")
        if ($this->domain !== '') {
            $headers['X-Company-Domain'] = $this->domain;
        }

        $this->client = new Client([
            'base_uri' => $baseUrl,
            'headers' => $headers,
            'timeout' => 30,
        ]);
        Log::info('MOCO base_uri set to: ' . $baseUrl . ' domain=' . $this->domain);
    }

    /**
     * Get all projects from MOCO
     *
     * @param array $params Query parameters (e.g., ['active' => true])
     * @return array
     */
    public function getProjects(array $params = []): array
    {
        try {
            $response = $this->client->get('projects', [
                'query' => $params,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('MOCO API Error (getProjects): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get a single project from MOCO
     *
     * @param int $projectId
     * @return array|null
     */
    public function getProject(int $projectId): ?array
    {
        try {
            $response = $this->client->get("projects/{$projectId}");
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error("MOCO API Error (getProject {$projectId}): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all users (employees) from MOCO
     *
     * @param array $params Query parameters
     * @return array
     */
    public function getUsers(array $params = []): array
    {
        try {
            // Some tenants expose people under /users or /people
            $response = $this->client->get('users', [
                'query' => $params,
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            if (!is_array($data)) { $data = []; }
            return $data;
        } catch (GuzzleException $e) {
            // Try fallback endpoint
            try {
                $response = $this->client->get('people', [ 'query' => $params ]);
                $data = json_decode($response->getBody()->getContents(), true);
                if (!is_array($data)) { $data = []; }
                return $data;
            } catch (GuzzleException $e2) {
                Log::error('MOCO API Error (getUsers): ' . $e->getMessage());
                throw $e2;
            }
        }
    }

    /**
     * Get a single user from MOCO
     *
     * @param int $userId
     * @return array|null
     */
    public function getUser(int $userId): ?array
    {
        try {
            $response = $this->client->get("users/{$userId}");
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error("MOCO API Error (getUser {$userId}): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get activities (time entries) from MOCO
     *
     * @param array $params Query parameters (e.g., ['from' => '2025-01-01', 'to' => '2025-12-31'])
     * @return array
     */
    public function getActivities(array $params = []): array
    {
        try {
            $response = $this->client->get('activities', [
                'query' => $params,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('MOCO API Error (getActivities): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get a single activity from MOCO
     *
     * @param int $activityId
     * @return array|null
     */
    public function getActivity(int $activityId): ?array
    {
        try {
            $response = $this->client->get("activities/{$activityId}");
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error("MOCO API Error (getActivity {$activityId}): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get project assignments from MOCO
     *
     * @param array $params Query parameters
     * @return array
     */
    public function getProjectAssignments(array $params = []): array
    {
        try {
            $response = $this->client->get('project_assignments', [
                'query' => $params,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('MOCO API Error (getProjectAssignments): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get absences from MOCO
     *
     * @param array $params Query parameters (e.g., ['from' => '2025-01-01', 'to' => '2025-12-31'])
     * @return array
     */
    public function getAbsences(array $params = []): array
    {
        try {
            $response = $this->client->get('schedules/absences', [
                'query' => $params,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('MOCO API Error (getAbsences): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Test the MOCO API connection
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $endpointsToProbe = ['projects', 'activities', 'users'];
            $lastError = null;
            foreach ($endpointsToProbe as $endpoint) {
                try {
                    $response = $this->client->get($endpoint, [
                        'query' => ['limit' => 1],
                    ]);
                    if ($response->getStatusCode() === 200) {
                        return true;
                    }
                } catch (GuzzleException $inner) {
                    // remember last error and try next endpoint
                    $status = method_exists($inner, 'getResponse') && $inner->getResponse() ? $inner->getResponse()->getStatusCode() : null;
                    $msg = $inner->getMessage();
                    $lastError = "endpoint=$endpoint status=" . ($status ?? 'n/a') . " msg=$msg";
                    continue;
                }
            }

            if ($lastError) {
                Log::error('MOCO API Connection Test Failed (all endpoints): ' . $lastError);
            } else {
                Log::error('MOCO API Connection Test Failed: unknown error');
            }
            return false;
        } catch (GuzzleException $e) {
            $status = method_exists($e, 'getResponse') && $e->getResponse() ? $e->getResponse()->getStatusCode() : null;
            $body = method_exists($e, 'getResponse') && $e->getResponse() ? (string) $e->getResponse()->getBody() : '';
            Log::error('MOCO API Connection Test Failed: ' . $e->getMessage() . ($status ? " (status {$status})" : '') . ($body ? " body: {$body}" : ''));
            return false;
        }
    }
}

