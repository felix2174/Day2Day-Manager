<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class MocoService
{
    protected $apiKey;
    protected $baseUrl;
    protected $timeout;
    protected $retryAttempts;

    public function __construct()
    {
        $this->apiKey = config('moco.api_key');
        $this->baseUrl = config('moco.base_url');
        $this->timeout = config('moco.timeout', 10);
        $this->retryAttempts = config('moco.retry_attempts', 3);
    }

    /**
     * Get the authorization header for MOCO API requests
     */
    protected function getAuthHeader(): array
    {
        return [
            'Authorization' => 'Token token=' . $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Make a request to the MOCO API
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl . $endpoint;
        $headers = $this->getAuthHeader();

        $attempts = 0;
        while ($attempts < $this->retryAttempts) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders($headers);

                if ($method === 'GET') {
                    $response = $response->get($url, $data);
                } elseif ($method === 'POST') {
                    $response = $response->post($url, $data);
                } elseif ($method === 'PUT') {
                    $response = $response->put($url, $data);
                } elseif ($method === 'DELETE') {
                    $response = $response->delete($url);
                }

                if ($response->successful()) {
                    return $response->json();
                }

                if ($response->status() === 401) {
                    throw new Exception('MOCO API: Unauthorized - Invalid API key');
                }

                if ($response->status() === 404) {
                    throw new Exception('MOCO API: Not Found - Endpoint does not exist');
                }

                throw new Exception('MOCO API Error: ' . $response->status() . ' - ' . $response->body());

            } catch (Exception $e) {
                $attempts++;
                Log::warning("MOCO API request failed (attempt {$attempts}): " . $e->getMessage());
                
                if ($attempts >= $this->retryAttempts) {
                    throw $e;
                }
                
                sleep(1); // Wait 1 second before retry
            }
        }

        throw new Exception('MOCO API: Max retry attempts reached');
    }

    /**
     * Verify if the API key is valid
     */
    public function verifyApiKey(): array
    {
        try {
            // Versuche zuerst den aktuellen Benutzer zu laden
            $endpoint = config('moco.endpoints.session', '/users/me');
            $response = $this->makeRequest('GET', $endpoint);
            return [
                'valid' => true,
                'user' => $response
            ];
        } catch (Exception $e) {
            // Falls /users/me fehlschlägt, versuche /users endpoint
            try {
                $endpoint = config('moco.endpoints.users', '/users');
                $response = $this->makeRequest('GET', $endpoint);
                return [
                    'valid' => true,
                    'user' => 'API Key valid, but /users/me endpoint not available'
                ];
            } catch (Exception $e2) {
            return [
                'valid' => false,
                    'error' => $e->getMessage() . ' | Fallback: ' . $e2->getMessage()
            ];
            }
        }
    }

    /**
     * Get all projects from MOCO
     */
    public function getProjects(array $params = []): array
    {
        $endpoint = config('moco.endpoints.projects', '/projects');
        return $this->makeRequest('GET', $endpoint, $params);
    }

    /**
     * Get a specific project by ID
     */
    public function getProject(int $id): array
    {
        $endpoint = config('moco.endpoints.projects', '/projects') . '/' . $id;
        return $this->makeRequest('GET', $endpoint);
    }

    /**
     * Get project activities/time entries
     */
    public function getProjectActivities(int $projectId, array $params = []): array
    {
        $endpoint = config('moco.endpoints.activities', '/activities');
        $params['project_id'] = $projectId;
        return $this->makeRequest('GET', $endpoint, $params);
    }

    /**
     * Get project assignments - use activities to find team members
     */
    public function getProjectAssignments(int $projectId): array
    {
        try {
            // Get activities to find team members
            $activities = $this->getProjectActivities($projectId, ['limit' => 1000]);
            $teamMembers = [];
            
            foreach ($activities as $activity) {
                if (isset($activity['user']['id'])) {
                    $userId = $activity['user']['id'];
                    if (!isset($teamMembers[$userId])) {
                        $teamMembers[$userId] = [
                            'user' => $activity['user'],
                            'total_hours' => 0,
                            'billable_hours' => 0,
                            'last_activity' => $activity['date']
                        ];
                    }
                    $teamMembers[$userId]['total_hours'] += $activity['hours'];
                    if ($activity['billable']) {
                        $teamMembers[$userId]['billable_hours'] += $activity['hours'];
                    }
                }
            }
            
            return array_values($teamMembers);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get project tasks - MOCO doesn't have separate tasks endpoint, use activities
     */
    public function getProjectTasks(int $projectId): array
    {
        try {
            $activities = $this->getProjectActivities($projectId, ['limit' => 1000]);
            $tasks = [];
            
            foreach ($activities as $activity) {
                if (isset($activity['task']['id'])) {
                    $taskId = $activity['task']['id'];
                    if (!isset($tasks[$taskId])) {
                        $tasks[$taskId] = [
                            'id' => $activity['task']['id'],
                            'name' => $activity['task']['name'],
                            'user' => $activity['user'],
                            'total_hours' => 0,
                            'billable_hours' => 0,
                            'last_activity' => $activity['date']
                        ];
                    }
                    $tasks[$taskId]['total_hours'] += $activity['hours'];
                    if ($activity['billable']) {
                        $tasks[$taskId]['billable_hours'] += $activity['hours'];
                    }
                }
            }
            
            return array_values($tasks);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get project milestones - MOCO doesn't have milestones endpoint
     */
    public function getProjectMilestones(int $projectId): array
    {
        // MOCO doesn't have a milestones endpoint, return empty array
        return [];
    }

    /**
     * Get project reports - MOCO doesn't have reports endpoint
     */
    public function getProjectReports(int $projectId): array
    {
        // MOCO doesn't have a reports endpoint, return empty array
        return [];
    }

    /**
     * Get comprehensive project data including all related information
     */
    public function getProjectComprehensive(int $projectId): array
    {
        try {
            $project = $this->getProject($projectId);
            
            // Get related data with error handling
            $activities = [];
            $assignments = [];
            $tasks = [];
            $milestones = [];
            $reports = [];
            
            try {
                $activities = $this->getProjectActivities($projectId, ['limit' => 100]);
            } catch (Exception $e) {
                \Log::warning("Failed to get project activities for project {$projectId}: " . $e->getMessage());
            }
            
            try {
                $assignments = $this->getProjectAssignments($projectId);
            } catch (Exception $e) {
                \Log::warning("Failed to get project assignments for project {$projectId}: " . $e->getMessage());
            }
            
            try {
                $tasks = $this->getProjectTasks($projectId);
            } catch (Exception $e) {
                \Log::warning("Failed to get project tasks for project {$projectId}: " . $e->getMessage());
            }
            
            try {
                $milestones = $this->getProjectMilestones($projectId);
            } catch (Exception $e) {
                \Log::warning("Failed to get project milestones for project {$projectId}: " . $e->getMessage());
            }
            
            try {
                $reports = $this->getProjectReports($projectId);
            } catch (Exception $e) {
                \Log::warning("Failed to get project reports for project {$projectId}: " . $e->getMessage());
            }
            
            return [
                'project' => $project,
                'activities' => $activities,
                'assignments' => $assignments,
                'tasks' => $tasks,
                'milestones' => $milestones,
                'reports' => $reports,
                'summary' => $this->calculateProjectSummary($project, $activities, $assignments, $tasks)
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'project' => null,
                'activities' => [],
                'assignments' => [],
                'tasks' => [],
                'milestones' => [],
                'reports' => [],
                'summary' => []
            ];
        }
    }

    /**
     * Calculate project summary statistics
     */
    private function calculateProjectSummary(array $project, array $activities, array $assignments, array $tasks): array
    {
        $totalHours = array_sum(array_column($activities, 'hours'));
        $totalCost = array_sum(array_column($activities, 'cost'));
        $billableHours = array_sum(array_filter(array_column($activities, 'hours'), function($activity) {
            return isset($activity['billable']) && $activity['billable'];
        }));
        
        $completedTasks = count(array_filter($tasks, function($task) {
            return isset($task['completed']) && $task['completed'];
        }));
        
        $totalTasks = count($tasks);
        $taskCompletionRate = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
        
        return [
            'total_hours' => $totalHours,
            'total_cost' => $totalCost,
            'billable_hours' => $billableHours,
            'non_billable_hours' => $totalHours - $billableHours,
            'completed_tasks' => $completedTasks,
            'total_tasks' => $totalTasks,
            'task_completion_rate' => $taskCompletionRate,
            'team_members' => count($assignments),
            'active_assignments' => count(array_filter($assignments, function($assignment) {
                return isset($assignment['active']) && $assignment['active'];
            }))
        ];
    }

    /**
     * Get all users from MOCO
     */
    public function getUsers(array $params = []): array
    {
        $endpoint = config('moco.endpoints.users', '/users');
        return $this->makeRequest('GET', $endpoint, $params);
    }

    /**
     * Get a specific user by ID
     */
    public function getUser(int $id): array
    {
        $endpoint = config('moco.endpoints.users', '/users') . '/' . $id;
        return $this->makeRequest('GET', $endpoint);
    }

    /**
     * Get all activities from MOCO
     */
    public function getActivities(array $params = []): array
    {
        $endpoint = config('moco.endpoints.activities', '/activities');
        return $this->makeRequest('GET', $endpoint, $params);
    }

    /**
     * Get all companies from MOCO
     */
    public function getCompanies(array $params = []): array
    {
        $endpoint = config('moco.endpoints.companies', '/companies');
        return $this->makeRequest('GET', $endpoint, $params);
    }

    /**
     * Get all contacts from MOCO
     */
    public function getContacts(array $params = []): array
    {
        $endpoint = config('moco.endpoints.contacts', '/contacts');
        return $this->makeRequest('GET', $endpoint, $params);
    }

    /**
     * Get all deals from MOCO
     */
    public function getDeals(array $params = []): array
    {
        $endpoint = config('moco.endpoints.deals', '/deals');
        return $this->makeRequest('GET', $endpoint, $params);
    }

    /**
     * Get all invoices from MOCO
     */
    public function getInvoices(array $params = []): array
    {
        $endpoint = config('moco.endpoints.invoices', '/invoices');
        return $this->makeRequest('GET', $endpoint, $params);
    }

    /**
     * Get all offers from MOCO
     */
    public function getOffers(array $params = []): array
    {
        $endpoint = config('moco.endpoints.offers', '/offers');
        return $this->makeRequest('GET', $endpoint, $params);
    }

    /**
     * Get planning entries from MOCO
     */
    public function getPlanningEntries(array $params = []): array
    {
        $endpoint = config('moco.endpoints.planning_entries', '/planning_entries');
        return $this->makeRequest('GET', $endpoint, $params);
    }

    /**
     * Get user profile information
     */
    public function getProfile(): array
    {
        $endpoint = config('moco.endpoints.profile', '/users/me');
        return $this->makeRequest('GET', $endpoint);
    }

    /**
     * Create a new project in MOCO
     */
    public function createProject(array $data): array
    {
        $endpoint = config('moco.endpoints.projects', '/projects');
        return $this->makeRequest('POST', $endpoint, $data);
    }

    /**
     * Update a project in MOCO
     */
    public function updateProject(int $id, array $data): array
    {
        $endpoint = config('moco.endpoints.projects', '/projects') . '/' . $id;
        return $this->makeRequest('PUT', $endpoint, $data);
    }

    /**
     * Delete a project in MOCO
     */
    public function deleteProject(int $id): array
    {
        $endpoint = config('moco.endpoints.projects', '/projects') . '/' . $id;
        return $this->makeRequest('DELETE', $endpoint);
    }

    /**
     * Sync projects from MOCO to local database
     */
    public function syncProjects(): array
    {
        try {
            $mocoProjects = $this->getProjects();
            $synced = [];
            $errors = [];

            foreach ($mocoProjects as $mocoProject) {
                try {
                    // Check if project already exists
                    $existingProject = \App\Models\Project::where('moco_id', $mocoProject['id'])->first();
                    
                    if ($existingProject) {
                        // Update existing project
                        $existingProject->update([
                            'name' => $mocoProject['name'],
                            'description' => $mocoProject['description'] ?? null,
                            'status' => $mocoProject['active'] ? 'active' : 'completed',
                            'start_date' => $mocoProject['created_at'] ?? null,
                            'end_date' => $mocoProject['updated_at'] ?? null,
                        ]);
                        $synced[] = ['action' => 'updated', 'project' => $existingProject];
                    } else {
                        // Create new project
                        $project = \App\Models\Project::create([
                            'moco_id' => $mocoProject['id'],
                            'name' => $mocoProject['name'],
                            'description' => $mocoProject['description'] ?? null,
                            'status' => $mocoProject['active'] ? 'active' : 'completed',
                            'start_date' => $mocoProject['created_at'] ?? null,
                            'end_date' => $mocoProject['updated_at'] ?? null,
                        ]);
                        $synced[] = ['action' => 'created', 'project' => $project];
                    }
                } catch (Exception $e) {
                    $errors[] = [
                        'moco_project_id' => $mocoProject['id'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            return [
                'success' => true,
                'synced' => $synced,
                'errors' => $errors,
                'total_moco_projects' => count($mocoProjects),
                'total_synced' => count($synced),
                'total_errors' => count($errors)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}









