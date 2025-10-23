<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Employee;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\Absence;
use App\Services\MocoService;
use App\Services\EmployeeKpiService;
use Exception;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use App\Models\Assignment;

class EmployeeController extends Controller
{
    /**
     * Export employees to CSV
     */
    public function export()
    {
        $employees = DB::table('employees')->where('is_active', true)->get();

        $filename = 'mitarbeiter-auslastung-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($employees) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM für Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header
            fputcsv($file, ['Mitarbeiter', 'Abteilung', 'Wochenkapazität (h)', 'Verplant (h)', 'Verfügbar (h)', 'Auslastung (%)'], ';');

            // Daten
            foreach ($employees as $employee) {
                $assignments = DB::table('assignments')
                    ->where('employee_id', $employee->id)
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->sum('weekly_hours');

                $utilization = $employee->weekly_capacity > 0
                    ? round(($assignments / $employee->weekly_capacity) * 100)
                    : 0;

                fputcsv($file, [
                    $employee->first_name . ' ' . $employee->last_name,
                    $employee->department,
                    $employee->weekly_capacity,
                    $assignments,
                    $employee->weekly_capacity - $assignments,
                    $utilization
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show import form
     */
    public function importForm()
    {
        return view('employees.import');
    }

    /**
     * Process import
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');
        
        // Skip header
        fgetcsv($handle, 1000, ';');
        
        $imported = 0;
        $errors = [];
        
        while (($data = fgetcsv($handle, 1000, ';')) !== false) {
            try {
                if (count($data) >= 6) {
                    Employee::create([
                        'first_name' => $data[0],
                        'last_name' => $data[1],
                        'department' => $data[2],
                        'weekly_capacity' => (int)$data[3],
                        'is_active' => $data[4] === '1' || strtolower($data[4]) === 'true',
                        'email' => $data[5] ?? null,
                    ]);
                    $imported++;
                }
            } catch (Exception $e) {
                $errors[] = "Zeile " . ($imported + 1) . ": " . $e->getMessage();
            }
        }
        
        fclose($handle);
        
        $message = "Erfolgreich {$imported} Mitarbeiter importiert.";
        if (!empty($errors)) {
            $message .= " Fehler: " . implode(', ', $errors);
        }
        
        return redirect()->route('employees.index')->with('success', $message);
    }

    /**
     * Display a listing of the employees.
     */
    public function index()
    {
        $employees = Employee::orderBy('timeline_order')
            ->get();

        $employeeKpiService = app(EmployeeKpiService::class);
        $kpiResult = $employeeKpiService->enrich($employees);
        $employees = $kpiResult['employees'];
        $kpiWarnings = $kpiResult['warnings'];
        $statusCounts = $kpiResult['status_counts'];

        // ==================== ABWESENHEITEN FÜR SIDEBAR ====================
        
        // Aktuelle und kommende Abwesenheiten (nächste 30 Tage)
        $upcomingAbsences = DB::table('absences')
            ->join('employees', 'absences.employee_id', '=', 'employees.id')
            ->where('absences.start_date', '<=', now()->addDays(30))
            ->where('absences.end_date', '>=', now())
            ->select(
                'absences.*',
                'employees.first_name',
                'employees.last_name',
                'employees.department',
                DB::raw("employees.first_name || ' ' || employees.last_name as employee_name")
            )
            ->orderBy('absences.start_date')
            ->get();
        
        // Gruppiere Abwesenheiten nach Typ für Statistik
        $absenceStats = [
            'total' => $upcomingAbsences->count(),
            'urlaub' => $upcomingAbsences->where('type', 'urlaub')->count(),
            'krankheit' => $upcomingAbsences->where('type', 'krankheit')->count(),
            'fortbildung' => $upcomingAbsences->where('type', 'fortbildung')->count(),
        ];
        
        // Berechne Team-Verfügbarkeit (Prozentsatz verfügbarer Mitarbeiter)
        $totalEmployees = DB::table('employees')->where('is_active', true)->count();
        $absentEmployees = DB::table('absences')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->distinct('employee_id')
            ->count();
        
        $teamAvailability = $totalEmployees > 0 
            ? round((($totalEmployees - $absentEmployees) / $totalEmployees) * 100, 1)
            : 100;

        // ==================== MOCO-DATEN ====================

        // Lade MOCO-Daten für jeden Mitarbeiter
        $mocoService = app(MocoService::class);
        
        foreach ($employees as $employee) {
            // Initialisiere MOCO-Daten
            $employee->moco_weekly_capacity = null;
            $employee->moco_planned_hours = 0;
            $employee->moco_utilization = 0;
            $employee->moco_free_hours = 0;
            
            if ($employee->moco_id) {
                try {
                    // 1. Hole User-Daten aus MOCO für work_schedule
                    $mocoUser = $mocoService->getUser($employee->moco_id);
                    $weeklyCapacity = 40; // Default
                    
                    if ($mocoUser) {
                        // MOCO kann work_schedule in verschiedenen Formaten speichern
                        
                        // Option 1: work_schedule als Array (monday, tuesday, etc.)
                        if (isset($mocoUser['work_schedule']) && is_array($mocoUser['work_schedule'])) {
                            $weeklyCapacity = array_sum($mocoUser['work_schedule']);
                            Log::info("MOCO: User {$employee->moco_id} work_schedule = {$weeklyCapacity}h");
                        }
                        // Option 2: custom_properties
                        elseif (isset($mocoUser['custom_properties'])) {
                            if (isset($mocoUser['custom_properties']['Wochenkapazität'])) {
                                $weeklyCapacity = (float) $mocoUser['custom_properties']['Wochenkapazität'];
                            } elseif (isset($mocoUser['custom_properties']['weekly_capacity'])) {
                                $weeklyCapacity = (float) $mocoUser['custom_properties']['weekly_capacity'];
                            }
                        }
                        // Option 3: Fallback auf lokale Datenbank
                        else {
                            $weeklyCapacity = $employee->weekly_capacity ?? 40;
                            Log::info("MOCO: User {$employee->moco_id} using local capacity = {$weeklyCapacity}h");
                        }
                        
                        $employee->moco_weekly_capacity = $weeklyCapacity;
                    } else {
                        // Fallback auf lokale Datenbank
                        $employee->moco_weekly_capacity = $employee->weekly_capacity ?? 40;
                    }
                    
                    // 2. Analysiere Activities und gruppiere nach Projekten
                    // Hole Activities der letzten 30 Tage
                    $thirtyDaysAgo = Carbon::now()->subDays(30);
                    $activities = $mocoService->getUserActivities($employee->moco_id, [
                        'from' => $thirtyDaysAgo->format('Y-m-d'),
                        'to' => Carbon::now()->format('Y-m-d')
                    ]);
                    
                    // Gruppiere nach Projekten
                    $projectHours = [];
                    $totalHours = 0;
                    
                    foreach ($activities as $activity) {
                        $hours = $activity['hours'] ?? 0;
                        $totalHours += $hours;
                        
                        // Projektname ermitteln
                        $projectName = 'Ohne Projekt';
                        $projectId = null;
                        
                        if (isset($activity['project']) && isset($activity['project']['name'])) {
                            $projectName = $activity['project']['name'];
                            $projectId = $activity['project']['id'] ?? null;
                        }
                        
                        if (!isset($projectHours[$projectName])) {
                            $projectHours[$projectName] = [
                                'name' => $projectName,
                                'id' => $projectId,
                                'hours' => 0
                            ];
                        }
                        
                        $projectHours[$projectName]['hours'] += $hours;
                    }
                    
                    // Sortiere nach Stunden (höchste zuerst)
                    usort($projectHours, function($a, $b) {
                        return $b['hours'] <=> $a['hours'];
                    });
                    
                    // Berechne Prozentanteile
                    $projectDistribution = [];
                    foreach ($projectHours as $project) {
                        $percentage = $totalHours > 0 ? round(($project['hours'] / $totalHours) * 100, 1) : 0;
                        $projectDistribution[] = [
                            'name' => $project['name'],
                            'id' => $project['id'],
                            'hours' => round($project['hours'], 1),
                            'percentage' => $percentage
                        ];
                    }
                    
                    $employee->moco_total_hours = round($totalHours, 1);
                    $employee->moco_project_distribution = $projectDistribution;
                    $employee->moco_activities_count = count($activities);
                    
                    // Berechne Auslastung basierend auf Gesamtstunden vs. maximale Kapazität (30 Tage)
                    $maxHours = $employee->moco_weekly_capacity * (30/7); // 30 Tage ≈ 4.29 Wochen
                    if ($maxHours > 0) {
                        $employee->moco_utilization = round(($totalHours / $maxHours) * 100);
                    } else {
                        $employee->moco_utilization = 0;
                    }
                    
                    Log::info("MOCO: User {$employee->moco_id} ({$employee->first_name} {$employee->last_name}) - {$totalHours}h in last 30 days across " . count($projectHours) . " projects (" . count($activities) . " activities)");
                    
                } catch (\Exception $e) {
                    Log::warning('Could not load MOCO utilization data for employee ' . $employee->id . ': ' . $e->getMessage());
                    
                    // Fallback: Keine Daten verfügbar
                    $employee->moco_weekly_capacity = $employee->weekly_capacity ?? 40;
                    $employee->moco_planned_hours = 0;
                    $employee->moco_utilization = 0;
                    $employee->moco_free_hours = $employee->moco_weekly_capacity;
                }
            } else {
                // Kein MOCO-Account: Keine Auslastungsdaten verfügbar
                $employee->moco_weekly_capacity = $employee->weekly_capacity ?? 40;
                $employee->moco_planned_hours = 0;
                $employee->moco_utilization = 0;
                $employee->moco_free_hours = $employee->moco_weekly_capacity;
            }
        }

        return view('employees.index', compact(
            'employees',
            'upcomingAbsences',
            'absenceStats',
            'teamAvailability',
            'kpiWarnings',
            'statusCounts'
        ));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'weekly_capacity' => ['nullable', 'numeric', 'min:0', 'max:80'],
            'phone' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        Employee::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'] ?? null,
            'department' => $validated['department'] ?? '',
            'weekly_capacity' => $validated['weekly_capacity'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'] ?? null,
            'position' => $validated['position'] ?? null,
            'hourly_rate' => $validated['hourly_rate'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('employees.index')->with('success', 'Mitarbeiter wurde angelegt.');
    }

    public function show(Employee $employee)
    {
        // Lade lokale Assignment-Daten
        $assignments = DB::table('assignments')
            ->join('projects', 'assignments.project_id', '=', 'projects.id')
            ->where('assignments.employee_id', $employee->id)
            ->select('assignments.*', 'projects.name as project_name', 'projects.moco_id as project_moco_id')
            ->get();

        // Lade lokale Zeiterfassung
        $timeEntries = TimeEntry::where('employee_id', $employee->id)
            ->with('project')
            ->orderBy('date', 'desc')
            ->take(20)
            ->get();

        // Lade lokale Abwesenheiten
        $absences = Absence::where('employee_id', $employee->id)
            ->orderBy('start_date', 'desc')
            ->get();

        // MOCO-Daten laden
        $mocoData = null;
        $mocoProjects = [];
        $mocoActivities = [];
        $mocoAbsences = [];
        $mocoAssignments = [];
        $mocoStats = [];

        if ($employee->moco_id) {
            try {
                $mocoService = app(MocoService::class);
                
                // MOCO Benutzer-Daten
                $mocoData = $mocoService->getUser($employee->moco_id);
                
                // MOCO Projekte (nur die, bei denen der Mitarbeiter zugeteilt ist)
                $mocoProjects = $mocoService->getUserProjects($employee->moco_id);
                
                // MOCO Activities (letzte 30 Tage)
                $thirtyDaysAgo = Carbon::now()->subDays(30);
                $mocoActivities = $mocoService->getUserActivities($employee->moco_id, [
                    'from' => $thirtyDaysAgo->format('Y-m-d'),
                    'to' => Carbon::now()->format('Y-m-d')
                ]);
                
                // Sortiere Activities nach Datum (neueste zuerst)
                usort($mocoActivities, function($a, $b) {
                    return strcmp($b['date'] ?? '', $a['date'] ?? '');
                });
                
                // MOCO Abwesenheiten (nächste 90 Tage)
                $mocoAbsences = $mocoService->getUserAbsences($employee->moco_id, [
                    'from' => Carbon::now()->format('Y-m-d'),
                    'to' => Carbon::now()->addDays(90)->format('Y-m-d')
                ]);
                
                // MOCO Project Assignments
                $mocoAssignments = $mocoService->getUserProjectAssignments($employee->moco_id);
                
                // Berechne MOCO-Statistiken
                $mocoStats = $this->calculateMocoStats($mocoActivities, $mocoProjects, $mocoAbsences);
                
            } catch (\Exception $e) {
                Log::warning('Could not load MOCO data for employee ' . $employee->id . ': ' . $e->getMessage());
            }
        }

        // Erstelle Projektliste NUR aus MOCO
        // Nur Projekte, denen der Mitarbeiter über MOCO Contracts zugewiesen ist
        $combinedProjects = collect();
        
        // Füge MOCO-Projekte hinzu (NUR die, bei denen der User in den Contracts ist)
        // getUserProjects() gibt bereits nur Projekte zurück, wo der User in contracts ist
        foreach ($mocoProjects as $mocoProject) {
            $mocoId = $mocoProject['id'];
            
            // Doppelte Prüfung: Nur wenn der User wirklich in den Contracts ist
            $userIsAssigned = false;
            if (isset($mocoProject['contracts']) && is_array($mocoProject['contracts'])) {
                foreach ($mocoProject['contracts'] as $contract) {
                    if (isset($contract['user_id']) && $contract['user_id'] == $employee->moco_id) {
                        $userIsAssigned = true;
                        break;
                    }
                }
            }
            
            // Nur hinzufügen, wenn der User tatsächlich zugewiesen ist
            if ($userIsAssigned) {
                $combinedProjects->put('moco_' . $mocoId, [
                    'id' => $mocoProject['id'],
                    'name' => $mocoProject['name'],
                    'moco_id' => $mocoProject['id'],
                    'source' => 'moco',
                    'identifier' => $mocoProject['identifier'] ?? null,
                    'active' => $mocoProject['active'] ?? false,
                    'billable' => $mocoProject['billable'] ?? false,
                    'start_date' => $mocoProject['start_date'] ?? null,
                    'finish_date' => $mocoProject['finish_date'] ?? null,
                    'leader' => $mocoProject['leader'] ?? null,
                    'hourly_rate' => $mocoProject['hourly_rate'] ?? null,
                    'budget' => $mocoProject['budget'] ?? null
                ]);
            }
        }

        // Berechne Projektverteilung aus MOCO Activities (letzte 4 Wochen)
        $projectHours = [];
        $totalHours = 0;
        
        foreach ($mocoActivities as $activity) {
            $hours = $activity['hours'] ?? 0;
            $totalHours += $hours;
            
            $projectName = 'Ohne Projekt';
            $projectId = null;
            
            if (isset($activity['project']) && isset($activity['project']['name'])) {
                $projectName = $activity['project']['name'];
                $projectId = $activity['project']['id'] ?? null;
            }
            
            if (!isset($projectHours[$projectName])) {
                $projectHours[$projectName] = [
                    'name' => $projectName,
                    'id' => $projectId,
                    'hours' => 0
                ];
            }
            
            $projectHours[$projectName]['hours'] += $hours;
        }
        
        // Sortiere nach Stunden
        usort($projectHours, function($a, $b) {
            return $b['hours'] <=> $a['hours'];
        });
        
        // Berechne Prozentanteile
        $projectDistribution = [];
        foreach ($projectHours as $project) {
            $percentage = $totalHours > 0 ? round(($project['hours'] / $totalHours) * 100, 1) : 0;
            $projectDistribution[] = [
                'name' => $project['name'],
                'id' => $project['id'],
                'hours' => round($project['hours'], 1),
                'percentage' => $percentage
            ];
        }

        return view('employees.show', compact(
            'employee', 
            'assignments', 
            'timeEntries', 
            'absences',
            'mocoData',
            'mocoProjects',
            'mocoActivities',
            'mocoAbsences',
            'mocoAssignments',
            'mocoStats',
            'combinedProjects',
            'projectDistribution',
            'totalHours'
        ));
    }

    /**
     * Calculate MOCO statistics
     */
    private function calculateMocoStats(array $activities, array $projects, array $absences): array
    {
        $totalHours = 0;
        $billableHours = 0;
        $totalCost = 0;
        $activeProjects = 0;
        $completedProjects = 0;
        $currentAbsences = 0;
        $futureAbsences = 0;

        // Activities Statistics
        foreach ($activities as $activity) {
            $hours = $activity['hours'] ?? 0;
            $totalHours += $hours;
            
            if ($activity['billable'] ?? false) {
                $billableHours += $hours;
                $hourlyRate = $activity['hourly_rate'] ?? 0;
                $totalCost += $hours * $hourlyRate;
            }
        }

        // Projects Statistics
        foreach ($projects as $project) {
            if ($project['active'] ?? false) {
                $activeProjects++;
            } else {
                $completedProjects++;
            }
        }

        // Absences Statistics
        $now = Carbon::now();
        foreach ($absences as $absence) {
            $startDate = Carbon::parse($absence['start_date'] ?? '');
            $endDate = Carbon::parse($absence['end_date'] ?? '');
            
            if ($startDate->lte($now) && $endDate->gte($now)) {
                $currentAbsences++;
            } elseif ($startDate->gt($now)) {
                $futureAbsences++;
            }
        }

        return [
            'total_hours' => $totalHours,
            'billable_hours' => $billableHours,
            'total_cost' => $totalCost,
            'active_projects' => $activeProjects,
            'completed_projects' => $completedProjects,
            'current_absences' => $currentAbsences,
            'future_absences' => $futureAbsences,
            'activities_count' => count($activities),
            'projects_count' => count($projects),
            'absences_count' => count($absences)
        ];
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'department' => 'nullable|string|max:255',
            'weekly_capacity' => 'nullable|numeric|min:0|max:80',
            'phone' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'hourly_rate' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $employee->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'] ?? null,
            'department' => $validated['department'] ?? '',
            'weekly_capacity' => $validated['weekly_capacity'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'] ?? null,
            'position' => $validated['position'] ?? null,
            'hourly_rate' => $validated['hourly_rate'] ?? null,
            'is_active' => $validated['is_active'] ?? $employee->is_active,
        ]);

        return redirect()->route('employees.index')->with('success', 'Mitarbeiter erfolgreich aktualisiert!');
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Mitarbeiter erfolgreich gelöscht!');
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

    /**
     * Get pie chart data for a specific time range
     */
    public function getPieChartData(Request $request, Employee $employee)
    {
        $mocoService = app(MocoService::class);
        
        // Bestimme Zeitraum
        $days = $request->get('days', 30);
        
        if ($days === 'custom') {
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
        } else {
            $endDate = Carbon::now()->format('Y-m-d');
            $startDate = Carbon::now()->subDays((int)$days)->format('Y-m-d');
        }
        
        // Hole Activities aus MOCO
        $activities = $mocoService->getUserActivities($employee->moco_id, [
            'from' => $startDate,
            'to' => $endDate
        ]);
        
        // Sortiere nach Datum
        usort($activities, function($a, $b) {
            return strcmp($b['date'] ?? '', $a['date'] ?? '');
        });
        
        // Gruppiere nach Projekten
        $projectHours = [];
        $totalHours = 0;
        
        foreach ($activities as $activity) {
            $hours = $activity['hours'] ?? 0;
            $totalHours += $hours;
            
            $projectName = 'Ohne Projekt';
            $projectId = null;
            
            if (isset($activity['project']) && isset($activity['project']['name'])) {
                $projectName = $activity['project']['name'];
                $projectId = $activity['project']['id'] ?? null;
            }
            
            if (!isset($projectHours[$projectName])) {
                $projectHours[$projectName] = [
                    'name' => $projectName,
                    'id' => $projectId,
                    'hours' => 0
                ];
            }
            
            $projectHours[$projectName]['hours'] += $hours;
        }
        
        // Sortiere nach Stunden (höchste zuerst)
        usort($projectHours, function($a, $b) {
            return $b['hours'] <=> $a['hours'];
        });
        
        // Berechne Prozentanteile
        $projectDistribution = [];
        foreach ($projectHours as $project) {
            $percentage = $totalHours > 0 ? round(($project['hours'] / $totalHours) * 100, 1) : 0;
            $projectDistribution[] = [
                'name' => $project['name'],
                'id' => $project['id'],
                'hours' => round($project['hours'], 1),
                'percentage' => $percentage
            ];
        }
        
        return response()->json([
            'totalHours' => round($totalHours, 1),
            'projectDistribution' => $projectDistribution,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    /**
     * Get activities data for a specific time range
     */
    public function getActivitiesData(Request $request, Employee $employee)
    {
        $mocoService = app(MocoService::class);
        
        // Bestimme Zeitraum
        $days = $request->get('days', 30);
        
        if ($days === 'custom') {
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
        } else {
            $endDate = Carbon::now()->format('Y-m-d');
            $startDate = Carbon::now()->subDays((int)$days)->format('Y-m-d');
        }
        
        // Hole Activities aus MOCO
        $activities = $mocoService->getUserActivities($employee->moco_id, [
            'from' => $startDate,
            'to' => $endDate
        ]);
        
        // Sortiere nach Datum (neueste zuerst)
        usort($activities, function($a, $b) {
            return strcmp($b['date'] ?? '', $a['date'] ?? '');
        });
        
        return response()->json([
            'activities' => $activities,
            'count' => count($activities),
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:employees,id',
        ]);

        foreach ($data['order'] as $position => $employeeId) {
            Employee::where('id', $employeeId)->update(['timeline_order' => $position]);
        }

        return response()->json(['status' => 'ok']);
    }

    public function updateAssignments(Request $request)
    {
        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:employees,id',
            'bars' => 'required|array',
            'bars.*.assignment_id' => 'required|integer|exists:assignments,id',
            'bars.*.employee_id' => 'nullable|integer|exists:employees,id',
            'bars.*.start_date' => 'nullable|date',
            'bars.*.end_date' => 'nullable|date',
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['order'] as $position => $employeeId) {
                Employee::where('id', $employeeId)->update(['timeline_order' => $position]);
            }

            foreach ($data['bars'] as $bar) {
                $assignment = Assignment::find($bar['assignment_id']);
                if (!$assignment) {
                    continue;
                }

                $updates = [];
                if (!empty($bar['start_date'])) {
                    $updates['start_date'] = $bar['start_date'];
                }
                if (!empty($bar['end_date'])) {
                    $updates['end_date'] = $bar['end_date'];
                }
                if (!empty($bar['employee_id']) && $assignment->employee_id !== (int) $bar['employee_id']) {
                    $updates['employee_id'] = $bar['employee_id'];
                }

                if (!empty($updates)) {
                    $assignment->update($updates);
                }
            }
        });

        return response()->json(['status' => 'ok']);
    }
}
