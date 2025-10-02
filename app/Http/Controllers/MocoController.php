<?php

namespace App\Http\Controllers;

use App\Services\MocoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class MocoController extends Controller
{
    protected $mocoService;

    public function __construct(MocoService $mocoService)
    {
        $this->mocoService = $mocoService;
    }

    /**
     * Display MOCO integration dashboard
     */
    public function index()
    {
        try {
            // Verify API connection
            $apiStatus = $this->mocoService->verifyApiKey();
            
            return view('moco.index', [
                'apiStatus' => $apiStatus,
                'apiKey' => config('moco.api_key'),
                'baseUrl' => config('moco.base_url')
            ]);
        } catch (Exception $e) {
            return view('moco.index', [
                'apiStatus' => ['valid' => false, 'error' => $e->getMessage()],
                'apiKey' => config('moco.api_key'),
                'baseUrl' => config('moco.base_url')
            ]);
        }
    }

    /**
     * Test API connection
     */
    public function testConnection(): JsonResponse
    {
        try {
            $result = $this->mocoService->verifyApiKey();
            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'valid' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get projects from MOCO
     */
    public function getProjects(Request $request): JsonResponse
    {
        try {
            $params = $request->only(['active', 'billable', 'company_id']);
            $projects = $this->mocoService->getProjects($params);
            
            return response()->json([
                'success' => true,
                'data' => $projects,
                'count' => count($projects)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users from MOCO
     */
    public function getUsers(Request $request): JsonResponse
    {
        try {
            $params = $request->only(['active', 'company_id']);
            $users = $this->mocoService->getUsers($params);
            
            return response()->json([
                'success' => true,
                'data' => $users,
                'count' => count($users)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get activities from MOCO
     */
    public function getActivities(Request $request): JsonResponse
    {
        try {
            $params = $request->only(['from', 'to', 'user_id', 'project_id']);
            $activities = $this->mocoService->getActivities($params);
            
            return response()->json([
                'success' => true,
                'data' => $activities,
                'count' => count($activities)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get companies from MOCO
     */
    public function getCompanies(Request $request): JsonResponse
    {
        try {
            $params = $request->only(['active']);
            $companies = $this->mocoService->getCompanies($params);
            
            return response()->json([
                'success' => true,
                'data' => $companies,
                'count' => count($companies)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get contacts from MOCO
     */
    public function getContacts(Request $request): JsonResponse
    {
        try {
            $params = $request->only(['company_id', 'active']);
            $contacts = $this->mocoService->getContacts($params);
            
            return response()->json([
                'success' => true,
                'data' => $contacts,
                'count' => count($contacts)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get deals from MOCO
     */
    public function getDeals(Request $request): JsonResponse
    {
        try {
            $params = $request->only(['company_id', 'user_id', 'state']);
            $deals = $this->mocoService->getDeals($params);
            
            return response()->json([
                'success' => true,
                'data' => $deals,
                'count' => count($deals)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get invoices from MOCO
     */
    public function getInvoices(Request $request): JsonResponse
    {
        try {
            $params = $request->only(['from', 'to', 'company_id', 'state']);
            $invoices = $this->mocoService->getInvoices($params);
            
            return response()->json([
                'success' => true,
                'data' => $invoices,
                'count' => count($invoices)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get offers from MOCO
     */
    public function getOffers(Request $request): JsonResponse
    {
        try {
            $params = $request->only(['company_id', 'user_id', 'state']);
            $offers = $this->mocoService->getOffers($params);
            
            return response()->json([
                'success' => true,
                'data' => $offers,
                'count' => count($offers)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get planning entries from MOCO
     */
    public function getPlanningEntries(Request $request): JsonResponse
    {
        try {
            $params = $request->only(['from', 'to', 'user_id', 'project_id']);
            $entries = $this->mocoService->getPlanningEntries($params);
            
            return response()->json([
                'success' => true,
                'data' => $entries,
                'count' => count($entries)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user profile from MOCO
     */
    public function getProfile(): JsonResponse
    {
        try {
            $profile = $this->mocoService->getProfile();
            
            return response()->json([
                'success' => true,
                'data' => $profile
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync projects from MOCO to local database
     */
    public function syncProjects(): JsonResponse
    {
        try {
            $result = $this->mocoService->syncProjects();
            
            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new project in MOCO
     */
    public function createProject(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'company_id' => 'nullable|integer',
                'user_id' => 'nullable|integer',
                'active' => 'boolean',
                'billable' => 'boolean',
                'fixed_price' => 'boolean',
                'budget' => 'nullable|numeric',
                'hourly_rate' => 'nullable|numeric',
            ]);

            $project = $this->mocoService->createProject($data);
            
            return response()->json([
                'success' => true,
                'data' => $project
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a project in MOCO
     */
    public function updateProject(Request $request, int $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'company_id' => 'nullable|integer',
                'user_id' => 'nullable|integer',
                'active' => 'boolean',
                'billable' => 'boolean',
                'fixed_price' => 'boolean',
                'budget' => 'nullable|numeric',
                'hourly_rate' => 'nullable|numeric',
            ]);

            $project = $this->mocoService->updateProject($id, $data);
            
            return response()->json([
                'success' => true,
                'data' => $project
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a project in MOCO
     */
    public function deleteProject(int $id): JsonResponse
    {
        try {
            $result = $this->mocoService->deleteProject($id);
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update employee capacities based on MOCO planning entries
     */
    public function updateCapacities(): JsonResponse
    {
        try {
            // Hole Planungseinträge aus MOCO
            $planningEntries = $this->mocoService->getPlanningEntries();
            
            if (!$planningEntries) {
                return response()->json(['success' => false, 'message' => 'Keine Planungseinträge gefunden']);
            }

            // Analysiere geplante Arbeitszeiten pro Mitarbeiter
            $employeePlanningHours = [];
            $updatedCount = 0;

            foreach ($planningEntries as $entry) {
                try {
                    if (!isset($entry['user']['id'])) {
                        continue;
                    }

                    $userId = $entry['user']['id'];
                    $hoursPerDay = $entry['hours_per_day'] ?? 0;
                    $startsOn = isset($entry['starts_on']) ? \Carbon\Carbon::parse($entry['starts_on']) : null;
                    $endsOn = isset($entry['ends_on']) ? \Carbon\Carbon::parse($entry['ends_on']) : null;

                    if (!$startsOn || !$endsOn || $hoursPerDay <= 0) {
                        continue;
                    }

                    // Berechne Anzahl Arbeitstage
                    $workDays = $startsOn->diffInDays($endsOn) + 1;
                    $totalHours = $hoursPerDay * $workDays;

                    // Gruppiere nach Mitarbeiter
                    if (!isset($employeePlanningHours[$userId])) {
                        $employeePlanningHours[$userId] = [
                            'name' => $entry['user']['firstname'] . ' ' . $entry['user']['lastname'],
                            'total_hours' => 0,
                            'total_days' => 0
                        ];
                    }
                    
                    $employeePlanningHours[$userId]['total_hours'] += $totalHours;
                    $employeePlanningHours[$userId]['total_days'] += $workDays;

                } catch (Exception $e) {
                    \Log::warn("Fehler beim Analysieren von Planungseintrag: " . $e->getMessage());
                }
            }

            // Aktualisiere Kapazitäten
            foreach ($employeePlanningHours as $userId => $data) {
                $averageHoursPerDay = $data['total_days'] > 0 ? $data['total_hours'] / $data['total_days'] : 0;
                $averageHoursPerWeek = $averageHoursPerDay * 5; // 5 Arbeitstage pro Woche
                
                // Finde lokalen Mitarbeiter
                $employee = \App\Models\Employee::where('moco_id', $userId)->first();
                if (!$employee) {
                    continue;
                }

                $oldCapacity = $employee->weekly_capacity;
                $newCapacity = $this->getRecommendedCapacity($averageHoursPerWeek, $oldCapacity);
                
                if ($newCapacity != $oldCapacity) {
                    $employee->update(['weekly_capacity' => $newCapacity]);
                    $updatedCount++;
                }
            }

            return response()->json([
                'success' => true, 
                'message' => "Kapazitäten erfolgreich aktualisiert! {$updatedCount} Mitarbeiter-Kapazitäten wurden angepasst.",
                'updated_count' => $updatedCount,
                'analyzed_employees' => count($employeePlanningHours)
            ]);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Fehler bei der Kapazitäts-Aktualisierung: ' . $e->getMessage()]);
        }
    }

    private function getRecommendedCapacity($averageHoursPerWeek, $currentCapacity): int
    {
        if ($averageHoursPerWeek == 0) {
            return $currentCapacity;
        }

        $recommended = round($averageHoursPerWeek);
        
        if ($recommended < 20) return 20;
        if ($recommended > 50) return 50;
        
        $standardCapacities = [20, 25, 30, 35, 40, 45, 50];
        $closest = $standardCapacities[0];
        $minDiff = abs($recommended - $closest);
        
        foreach ($standardCapacities as $capacity) {
            $diff = abs($recommended - $capacity);
            if ($diff < $minDiff) {
                $minDiff = $diff;
                $closest = $capacity;
            }
        }
        
        return $closest;
    }
}
