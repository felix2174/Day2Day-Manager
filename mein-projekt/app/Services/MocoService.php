<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Illuminate\Support\Facades\Cache;

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
     * Get activities for a specific user
     *
     * @param int $userId
     * @param array $params Query parameters
     * @return array
     */
    public function getUserActivities(int $userId, array $params = []): array
    {
        try {
            $response = $this->client->get('activities', [
                'query' => array_merge([
                    'user_id' => $userId,
                    'limit' => 200,
                    'page' => 1
                ], $params)
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error("MOCO API Error (getUserActivities {$userId}): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get projects for a specific user (from contracts)
     *
     * @param int $userId
     * @return array
     */
    public function getUserProjects(int $userId): array
    {
        try {
            // Verwende die MOCO API Filterung direkt, falls verfügbar
            // Ansonsten hole alle Projekte und filtere manuell nach User-ID in Contracts
            $allProjects = $this->getProjects([
                'limit' => 500,  // Erhöhtes Limit für mehr Projekte
            ]);
            
            $userProjects = [];
            
            foreach ($allProjects as $project) {
                $isAssigned = false;
                
                // Prüfe ob der User in den Contracts des Projekts ist
                if (isset($project['contracts']) && is_array($project['contracts'])) {
                    foreach ($project['contracts'] as $contract) {
                        if (isset($contract['user_id']) && $contract['user_id'] == $userId) {
                            $isAssigned = true;
                            break;
                        }
                    }
                }
                
                // Nur Projekte hinzufügen, wo der User tatsächlich zugewiesen ist
                // und es sich um echte Projekte handelt (keine internen Einträge)
                if ($isAssigned && !$this->isInternalProject($project)) {
                    $userProjects[] = $project;
                }
            }
            
            Log::info("MOCO: Found " . count($userProjects) . " assigned projects for user {$userId} out of " . count($allProjects) . " total projects");
            
            return $userProjects;
        } catch (GuzzleException $e) {
            Log::error("MOCO API Error (getUserProjects {$userId}): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get absences for a specific user
     *
     * @param int $userId
     * @param array $params Query parameters
     * @return array
     */
    public function getUserAbsences(int $userId, array $params = []): array
    {
        try {
            $response = $this->client->get('schedules/absences', [
                'query' => array_merge([
                    'user_id' => $userId,
                    'limit' => 200,
                    'page' => 1
                ], $params)
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error("MOCO API Error (getUserAbsences {$userId}): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get project assignments for a specific user
     *
     * @param int $userId
     * @return array
     */
    public function getUserProjectAssignments(int $userId): array
    {
        try {
            $response = $this->client->get('project_assignments', [
                'query' => [
                    'user_id' => $userId,
                    'limit' => 200,
                    'page' => 1
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error("MOCO API Error (getUserProjectAssignments {$userId}): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get session information (authenticated user and company)
     * This is a lightweight endpoint to test the API connection.
     *
     * @return array|null
     * @throws GuzzleException
     */
    public function getSessionInfo(): ?array
    {
        try {
            $response = $this->client->get('session');
            $data = json_decode($response->getBody()->getContents(), true);
            
            if (is_array($data)) {
                Log::info('MOCO session info retrieved successfully');
                return $data;
            }
            
            return null;
        } catch (GuzzleException $e) {
            // If session endpoint doesn't exist (404), return null instead of throwing
            if ($e->getCode() === 404) {
                Log::info('MOCO session endpoint not available (404)');
                return null;
            }
            
            // For other errors, re-throw to be handled by the caller
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
                    $msg = $inner->getMessage();
                    $lastError = "endpoint=$endpoint msg=$msg";
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
            Log::error('MOCO API Connection Test Failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Holt die Teammitglieder eines MOCO-Projekts und cacht das Ergebnis.
     *
     * @param int $mocoProjectId Die MOCO ID des Projekts.
     * @return string|null Eine kommaseparierte Liste der Teammitglieder oder null bei Fehler.
     */
    public function getProjectTeam(int $mocoProjectId): ?array
    {
        $cacheKey = 'moco:project_team:' . $mocoProjectId;

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($mocoProjectId) {
            try {
                $mocoProject = $this->getProject($mocoProjectId);
                if ($mocoProject && isset($mocoProject['contracts'])) {
                    $members = collect();
                    foreach ($mocoProject['contracts'] as $contract) {
                        $userId = $contract['user_id'] ?? null;
                        if (!$userId) {
                            continue;
                        }

                        // User-Daten entweder aus Contract oder separat laden
                        $user = $contract['user'] ?? null;
                        if (!$user) {
                            // User nicht im Contract eingebettet, separat laden
                            $user = $this->getUser($userId);
                        }

                        if (!$user) {
                            continue; // User konnte nicht geladen werden
                        }

                        $firstName = $user['firstname'] ?? '';
                        $lastName = $user['lastname'] ?? '';
                        $fullName = trim($firstName . ' ' . $lastName);

                        $members->push([
                            'user_id' => $userId,
                            'name' => $fullName !== '' ? $fullName : ($user['display_name'] ?? 'Unknown'),
                            'role' => $contract['role'] ?? null,
                            'hours_per_week' => $contract['hours_per_week'] ?? $contract['hours_per_day'] ?? null,
                            'start_date' => $contract['start_date'] ?? null,
                            'end_date' => $contract['finish_date'] ?? $contract['end_date'] ?? null,
                        ]);
                    }

                    return $members->values()->all();
                }
            } catch (\Exception $e) {
                Log::warning('MOCO project team fetch failed for project ' . $mocoProjectId . ': ' . $e->getMessage());
            }
            return null;
        });
    }

    /**
     * Get project assignments for a specific project and cache the result.
     *
     * @param int $projectId The MOCO project ID.
     * @return array The project assignments.
     */
    public function getProjectAssignmentsCached(int $projectId)
    {
        $cacheKey = 'moco:project_assignments:' . $projectId;

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($projectId) {
            try {
                $response = $this->client->get('project_assignments', [
                    'query' => ['project_id' => $projectId, 'limit' => 200],
                ]);
                $data = json_decode($response->getBody()->getContents(), true);
                return is_array($data) ? $data : [];
            } catch (GuzzleException $e) {
                Log::warning('MOCO project assignments fetch failed for project ' . $projectId . ': ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Check if a project is an internal project (not a real customer project)
     * 
     * Internal projects include:
     * - Projects with specific tags like "Aufträge auf Zuruf"
     * - Projects with names starting with "Internes/"
     * - Projects with specific name patterns
     * 
     * @param array $project
     * @return bool
     */
    private function isInternalProject(array $project): bool
    {
        $name = $project['name'] ?? '';
        $tags = $project['tags'] ?? [];
        $labels = $project['labels'] ?? [];
        
        // Check for specific tags (when available from full project data)
        if (in_array('Aufträge auf Zuruf', $tags)) {
            return true;
        }
        
        // Check for specific labels (when available from full project data)
        if (in_array('Aufträge auf Zuruf', $labels)) {
            return true;
        }
        
        // Check for internal project names (most reliable for activities)
        $internalProjectNames = [
            'Aufträge auf Zuruf',
            'Internes/Wochenmeetings',
        ];
        
        foreach ($internalProjectNames as $internalName) {
            if ($name === $internalName) {
                return true;
            }
        }
        
        // Check for internal name patterns (for variations)
        $internalPatterns = [
            'Internes/',
            'Internes:',
            'Wochenmeetings',
            'Internal/',
            'Internal:',
        ];
        
        foreach ($internalPatterns as $pattern) {
            if (str_contains($name, $pattern)) {
                return true;
            }
        }
        
        return false;
    }
}

