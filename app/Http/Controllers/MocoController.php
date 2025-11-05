<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\MocoSyncLog;
use App\Services\MocoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class MocoController extends Controller
{
    protected MocoService $mocoService;

    public function __construct(MocoService $mocoService)
    {
        $this->mocoService = $mocoService;
    }

    /**
     * Helper: Return JSON or Redirect based on request type
     */
    protected function handleSyncResponse(Request $request, bool $success, string $message, string $output = '')
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
                'output' => $output
            ], $success ? 200 : 500);
        }

        return redirect()->back()->with(
            $success ? 'success' : 'error',
            $message
        );
    }

    /**
     * Show MOCO integration dashboard
     */
    public function index()
    {
        // Test MOCO connection
        $connectionStatus = $this->mocoService->testConnection();

        // Get statistics
        $stats = [
            'employees' => [
                'total' => Employee::count(),
                'synced' => Employee::whereNotNull('moco_id')->count(),
            ],
            'projects' => [
                'total' => Project::count(),
                'synced' => Project::whereNotNull('moco_id')->count(),
            ],
            'timeEntries' => [
                'total' => TimeEntry::count(),
                'synced' => TimeEntry::whereNotNull('moco_id')->count(),
            ],
        ];

        // Get recent sync logs
        $recentLogs = MocoSyncLog::with('user')
            ->orderBy('started_at', 'desc')
            ->limit(10)
            ->get();

        // Get last successful sync per type
        $lastSyncs = [
            'employees' => MocoSyncLog::ofType('employees')->successful()->latest('completed_at')->first(),
            'projects' => MocoSyncLog::ofType('projects')->successful()->latest('completed_at')->first(),
            'activities' => MocoSyncLog::ofType('activities')->successful()->latest('completed_at')->first(),
            'absences' => MocoSyncLog::ofType('absences')->successful()->latest('completed_at')->first(),
            'contracts' => MocoSyncLog::ofType('contracts')->successful()->latest('completed_at')->first(),
            // 'all' entfernt - nicht mehr verwendet (alte Vollständige Sync)
        ];

        $syncWarnings = [];
        $typeLabels = [
            'employees' => 'Mitarbeiter',
            'projects' => 'Projekte',
            'activities' => 'Zeiterfassungen',
            'absences' => 'Abwesenheiten',
            'contracts' => 'Zuweisungen',
            // 'all' entfernt - nicht mehr verwendet
        ];
        $warningThreshold = now()->subHours(24);

        foreach ($lastSyncs as $type => $log) {
            if (!$log) {
                $syncWarnings[] = "Noch keine erfolgreiche Synchronisation für {$typeLabels[$type]} durchgeführt.";
                continue;
            }

            if ($log->completed_at && $log->completed_at->lt($warningThreshold)) {
                $syncWarnings[] = "Letzter erfolgreicher {$typeLabels[$type]}-Sync vor " . $log->completed_at->diffForHumans();
            }
        }

        $lastFailedSync = MocoSyncLog::failed()->latest('started_at')->first();
        $lastConnectionCheck = Cache::get('moco:last_connection_check');

        return view('moco.index', [
            'connectionStatus' => $connectionStatus,
            'stats' => $stats,
            'recentLogs' => $recentLogs,
            'lastSyncs' => $lastSyncs,
            'syncWarnings' => $syncWarnings,
            'lastFailedSync' => $lastFailedSync,
            'lastConnectionCheck' => $lastConnectionCheck,
        ]);
    }

    /**
     * Sync employees from MOCO
     */
    public function syncEmployees(Request $request)
    {
        try {
            $options = [];
            if ($request->boolean('active_only')) {
                $options['--active'] = true;
            }

            Artisan::call('moco:sync-employees', $options);
            $output = Artisan::output();

            return $this->handleSyncResponse($request, true, 'Mitarbeiter erfolgreich synchronisiert!', $output);
        } catch (\Exception $e) {
            Log::error('MOCO Employees Sync Error: ' . $e->getMessage());
            return $this->handleSyncResponse($request, false, 'Fehler bei der Synchronisation: ' . $e->getMessage());
        }
    }

    /**
     * Sync projects from MOCO
     */
    public function syncProjects(Request $request)
    {
        try {
            $options = [];
            if ($request->boolean('active_only')) {
                $options['--active'] = true;
            }

            Artisan::call('moco:sync-projects', $options);
            $output = Artisan::output();

            return $this->handleSyncResponse($request, true, 'Projekte erfolgreich synchronisiert!', $output);
        } catch (\Exception $e) {
            Log::error('MOCO Project Sync Error: ' . $e->getMessage());
            return $this->handleSyncResponse($request, false, 'Fehler bei der Synchronisation: ' . $e->getMessage());
        }
    }

    /**
     * Sync activities from MOCO
     */
    public function syncActivities(Request $request)
    {
        try {
            $options = [];
            
            if ($request->has('from')) {
                $options['--from'] = $request->input('from');
            }
            
            if ($request->has('to')) {
                $options['--to'] = $request->input('to');
            }
            
            if ($request->has('days')) {
                $options['--days'] = $request->input('days', 30);
            }

            Artisan::call('moco:sync-activities', $options);
            $output = Artisan::output();

            return $this->handleSyncResponse(
                $request,
                true,
                'Zeiterfassungen erfolgreich synchronisiert!',
                $output
            );
        } catch (\Exception $e) {
            Log::error('MOCO Activities Sync Error: ' . $e->getMessage());
            
            return $this->handleSyncResponse(
                $request,
                false,
                'Fehler bei der Synchronisation: ' . $e->getMessage()
            );
        }
    }

    /**
     * Sync all data from MOCO
     */
    public function syncAll(Request $request)
    {
        try {
            $results = [];
            $startTime = now();
            
            // 1. Mitarbeiter synchronisieren
            try {
                Artisan::call('moco:sync-employees', ['--active' => $request->boolean('active_only')]);
                $results['employees'] = '✅ Mitarbeiter synchronisiert';
            } catch (\Exception $e) {
                $results['employees'] = '❌ Mitarbeiter-Fehler: ' . $e->getMessage();
            }
            
            // 2. Projekte synchronisieren
            try {
                Artisan::call('moco:sync-projects', ['--active' => $request->boolean('active_only')]);
                $results['projects'] = '✅ Projekte synchronisiert';
            } catch (\Exception $e) {
                $results['projects'] = '❌ Projekte-Fehler: ' . $e->getMessage();
            }
            
            // 3. Zeiterfassungen synchronisieren
            try {
                $days = $request->input('days', 30);
                Artisan::call('moco:sync-activities', ['--days' => $days]);
                $results['activities'] = '✅ Zeiterfassungen synchronisiert';
            } catch (\Exception $e) {
                $results['activities'] = '❌ Zeiterfassungen-Fehler: ' . $e->getMessage();
            }
            
            // 4. Abwesenheiten synchronisieren
            try {
                Artisan::call('moco:sync-absences');
                $results['absences'] = '✅ Abwesenheiten synchronisiert';
            } catch (\Exception $e) {
                $results['absences'] = '❌ Abwesenheiten-Fehler: ' . $e->getMessage();
            }
            
            $duration = now()->diffInSeconds($startTime);
            $successCount = collect($results)->filter(fn($r) => str_starts_with($r, '✅'))->count();
            $failCount = collect($results)->filter(fn($r) => str_starts_with($r, '❌'))->count();

            // AJAX-Support für Progress-Anzeige
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => $failCount === 0,
                    'message' => "Synchronisation abgeschlossen: {$successCount} erfolgreich, {$failCount} fehlgeschlagen ({$duration}s)",
                    'results' => $results,
                    'stats' => [
                        'success' => $successCount,
                        'failed' => $failCount,
                        'duration' => $duration,
                    ]
                ]);
            }

            return redirect()->back()->with('success', "Synchronisation abgeschlossen: {$successCount}/4 erfolgreich ({$duration}s)");
        } catch (\Exception $e) {
            Log::error('MOCO Full Sync Error: ' . $e->getMessage());
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fehler bei der Synchronisation: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Fehler bei der Synchronisation: ' . $e->getMessage());
        }
    }

    /**
     * Test MOCO API connection
     */
    public function testConnection()
    {
        try {
            $isConnected = $this->mocoService->testConnection();

            if ($isConnected) {
                return redirect()->back()->with('success', 'Verbindung zur MOCO API erfolgreich!');
            } else {
                return redirect()->back()->with('error', 'Verbindung zur MOCO API fehlgeschlagen. Bitte prüfen Sie Ihre Zugangsdaten.');
            }
        } catch (\Exception $e) {
            Log::error('MOCO Connection Test Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Fehler beim Verbindungstest: ' . $e->getMessage());
        }
    }

    /**
     * Show sync history/logs
     */
    public function logs(Request $request)
    {
        $query = MocoSyncLog::with('user')->orderBy('started_at', 'desc');

        // Filter by type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('sync_type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $logs = $query->paginate(20);

        return view('moco.logs', [
            'logs' => $logs,
            'currentType' => $request->get('type', 'all'),
            'currentStatus' => $request->get('status', 'all'),
        ]);
    }

    /**
     * Show mapping management (items without MOCO ID)
     */
    public function mappings()
    {
        $unmappedEmployees = Employee::whereNull('moco_id')->get();
        $unmappedProjects = Project::whereNull('moco_id')->get();
        $unmappedTimeEntries = TimeEntry::whereNull('moco_id')->get();

        return view('moco.mappings', [
            'unmappedEmployees' => $unmappedEmployees,
            'unmappedProjects' => $unmappedProjects,
            'unmappedTimeEntries' => $unmappedTimeEntries,
        ]);
    }

    /**
     * Show statistics and insights
     */
    public function statistics()
    {
        // Sync statistics by month
        $syncStats = MocoSyncLog::selectRaw('
            DATE_FORMAT(started_at, "%Y-%m") as month,
            sync_type,
            COUNT(*) as total_syncs,
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as successful,
            SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed,
            SUM(items_created) as total_created,
            SUM(items_updated) as total_updated,
            AVG(duration_seconds) as avg_duration
        ')
        ->where('started_at', '>=', Carbon::now()->subMonths(6))
        ->groupBy('month', 'sync_type')
        ->orderBy('month', 'desc')
        ->get();

        // Overall statistics
        $overallStats = [
            'total_syncs' => MocoSyncLog::count(),
            'successful_syncs' => MocoSyncLog::where('status', 'completed')->count(),
            'failed_syncs' => MocoSyncLog::where('status', 'failed')->count(),
            'total_items_synced' => MocoSyncLog::sum('items_processed'),
            'avg_duration' => MocoSyncLog::where('status', 'completed')->avg('duration_seconds'),
        ];

        // Data coverage
        $coverage = [
            'employees' => [
                'total' => Employee::count(),
                'synced' => Employee::whereNotNull('moco_id')->count(),
                'percentage' => Employee::count() > 0 
                    ? round((Employee::whereNotNull('moco_id')->count() / Employee::count()) * 100, 2)
                    : 0,
            ],
            'projects' => [
                'total' => Project::count(),
                'synced' => Project::whereNotNull('moco_id')->count(),
                'percentage' => Project::count() > 0 
                    ? round((Project::whereNotNull('moco_id')->count() / Project::count()) * 100, 2)
                    : 0,
            ],
            'timeEntries' => [
                'total' => TimeEntry::count(),
                'synced' => TimeEntry::whereNotNull('moco_id')->count(),
                'percentage' => TimeEntry::count() > 0 
                    ? round((TimeEntry::whereNotNull('moco_id')->count() / TimeEntry::count()) * 100, 2)
                    : 0,
            ],
        ];

        return view('moco.statistics', [
            'syncStats' => $syncStats,
            'overallStats' => $overallStats,
            'coverage' => $coverage,
        ]);
    }

    /**
     * Debug: Get MOCO Users as JSON
     */
    public function debugUsers()
    {
        try {
            $users = $this->mocoService->getUsers();
            return response()->json($users, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Debug: Get MOCO Projects as JSON
     */
    public function debugProjects()
    {
        try {
            $projects = $this->mocoService->getProjects();
            return response()->json($projects, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Debug: Get MOCO Activities as JSON
     */
    public function debugActivities()
    {
        try {
            $activities = $this->mocoService->getActivities();
            return response()->json($activities, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Debug: Get MOCO Absences as JSON
     */
    public function debugAbsences()
    {
        try {
            $absences = $this->mocoService->getAbsences();
            return response()->json($absences, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Debug: Get specific user data from MOCO as JSON
     */
    public function debugUser($userId)
    {
        try {
            $userData = $this->mocoService->getUser($userId);
            $userActivities = $this->mocoService->getUserActivities($userId);
            $userProjects = $this->mocoService->getUserProjects($userId);
            $userAbsences = $this->mocoService->getUserAbsences($userId);
            
            $data = [
                'user' => $userData,
                'activities' => $userActivities,
                'projects' => $userProjects,
                'absences' => $userAbsences
            ];
            
            return response()->json($data, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Debug: Get specific project data from MOCO as JSON
     */
    public function debugProject($projectId)
    {
        try {
            $projectData = $this->mocoService->getProject($projectId);
            
            $data = [
                'project' => $projectData,
                'note' => 'Für detaillierte Projekt-Daten (Tasks, Contracts, Customer) verwenden Sie die Projekt-Detail-Ansicht in der Anwendung.'
            ];
            
            return response()->json($data, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Synchronisiert Zeiterfassungen aus MOCO
     * Route: POST /moco/sync-time-entries
     */
    public function syncTimeEntries(Request $request)
    {
        try {
            $days = $request->input('days', 7);
            
            // Artisan Command aufrufen
            Artisan::call('sync:moco-time-entries', [
                '--days' => $days,
            ]);
            
            $output = Artisan::output();
            
            // Extrahiere Statistiken aus Command-Output (falls vorhanden)
            $message = "Zeiterfassungen der letzten {$days} Tage synchronisiert";
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'output' => trim($output),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fehler bei der Synchronisation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Synchronisiert Abwesenheiten aus MOCO
     * Route: POST /moco/sync-absences
     */
    public function syncAbsences(Request $request)
    {
        try {
            $days = $request->input('days', 30);
            
            // Artisan Command aufrufen
            Artisan::call('sync:moco-absences', [
                '--days' => $days,
            ]);
            
            $output = Artisan::output();
            
            // Extrahiere Statistiken aus Command-Output
            preg_match('/(\d+)\s+Abwesenheiten/', $output, $matches);
            $count = $matches[1] ?? 0;
            
            $message = "{$count} Abwesenheiten synchronisiert (letzte {$days} Tage + 6 Monate voraus)";
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'output' => trim($output),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fehler bei der Synchronisation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Synchronisiert Mitarbeiter-Zuweisungen aus MOCO Contracts
     * Route: POST /moco/sync-contracts
     */
    public function syncContracts(Request $request)
    {
        try {
            // Artisan Command aufrufen
            Artisan::call('sync:moco-contracts');
            
            $output = Artisan::output();
            
            // Extrahiere Statistiken aus Command-Output
            // Format: "✅ Erstellt/Aktualisiert | 123 | Assignments aus MOCO Contracts"
            preg_match('/Erstellt\/Aktualisiert.*?(\d+)/', $output, $createdMatches);
            preg_match('/Übersprungen.*?(\d+)/', $output, $skippedMatches);
            preg_match('/Keine Contracts.*?(\d+)/', $output, $noContractsMatches);
            preg_match('/Fehler.*?(\d+)/', $output, $errorMatches);
            
            $created = $createdMatches[1] ?? 0;
            $skipped = $skippedMatches[1] ?? 0;
            $noContracts = $noContractsMatches[1] ?? 0;
            $errors = $errorMatches[1] ?? 0;
            
            // Zähle Warnungen für externe Mitarbeiter
            $warnings = substr_count($output, '⚠️');
            
            // User-freundliche Nachricht generieren
            if ($created > 0) {
                $message = "✅ {$created} Mitarbeiter-Zuweisungen erstellt/aktualisiert";
            } elseif ($skipped > 0) {
                $message = "✅ Alle {$skipped} Zuweisungen sind bereits aktuell";
            } else {
                $message = "ℹ️  Keine Änderungen vorgenommen";
            }
            
            if ($warnings > 0) {
                $message .= " | {$warnings} externe Mitarbeiter (nicht in lokaler DB)";
            }
            
            if ($noContracts > 0) {
                $message .= " | {$noContracts} Projekte ohne MOCO-Contracts";
            }
            
            if ($errors > 0) {
                $message .= " | ⚠️ {$errors} Fehler aufgetreten";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'output' => trim($output),
                'stats' => [
                    'created' => (int)$created,
                    'skipped' => (int)$skipped,
                    'no_contracts' => (int)$noContracts,
                    'errors' => (int)$errors,
                    'warnings' => $warnings,
                ],
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fehler bei der Synchronisation: ' . $e->getMessage(),
            ], 500);
        }
    }
}

