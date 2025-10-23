<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Employee;
use App\Models\Assignment;
use App\Models\ProjectAssignmentOverride;
use App\Services\MocoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Exception;

class ProjectController extends Controller
{
    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {
        // Keine lokalen Zuweisungen mehr laden - nur MOCO-Daten

        // Keine Bottleneck-Berechnung mehr - nur MOCO-Daten
        $bottlenecks = [];
        

        // Keine lokalen Statistiken mehr - nur MOCO-Daten

        // Lade MOCO-Daten für das Projekt
        $mocoData = null;
        $mocoTasks = [];
        $mocoContracts = [];
        $mocoCustomer = null;
        
        if ($project->moco_id) {
            try {
                $mocoService = app(MocoService::class);
                $mocoData = $mocoService->getProject($project->moco_id);
                if ($mocoData) {
                    $mocoTasks = $mocoData['tasks'] ?? [];
                    $mocoContracts = $mocoData['contracts'] ?? [];
                    $mocoCustomer = $mocoData['customer'] ?? null;
                }
            } catch (\Exception $e) {
                // MOCO-Daten nicht verfügbar - continue ohne Fehler
                Log::warning('Could not load MOCO data for project ' . $project->id . ': ' . $e->getMessage());
            }
        }

        // Lade Projekt-Statistiken (nur MOCO-Daten)
        $projectStats = [
            'total_time_logged' => 0,
            'total_billable_hours' => 0,
            'total_cost' => 0,
            'time_entries_count' => 0,
            'assignments_count' => 0,
            'bottlenecks_count' => 0,
            'moco_tasks_count' => count($mocoTasks),
            'moco_contracts_count' => count($mocoContracts)
        ];

        // Lade Team-Mitglieder für das Projekt
        $projectTeams = [];
        if ($project->moco_id) {
            $mocoService = app(MocoService::class);
            $teamMembers = $mocoService->getProjectTeam($project->moco_id);
            if ($teamMembers) {
                $projectTeams[$project->moco_id] = $teamMembers;
            }
        }

        return view('projects.show', compact('project', 'bottlenecks', 'projectStats', 'mocoData', 'mocoTasks', 'mocoContracts', 'mocoCustomer', 'projectTeams'));
    }

    public function index()
    {
        $projects = Project::withCount('assignments')
            ->with(['assignments.employee', 'responsible'])
            ->where('name', '!=', 'Aufträge auf Zuruf')
            ->get();

        // Status-Berechnung aus bereits synchronisierten Daten (KEINE API-Aufrufe bei jedem Refresh)
        foreach ($projects as $project) {
            // Verwende die bereits synchronisierten Status aus der Datenbank
            // Diese werden durch die syncProjectStatuses() Funktion aktualisiert
            if ($project->status === 'completed') {
                $project->calculated_status = 'Abgeschlossen';
            } elseif ($project->status === 'active') {
                $project->calculated_status = 'In Bearbeitung';
            } else {
                // Fallback: Status "In Bearbeitung"
                $project->calculated_status = 'In Bearbeitung';
            }
        }

        // Lade Team-Mitglieder für alle Projekte effizient (MOCO + lokale Daten)
        $projectTeams = [];
        $mocoProjectIds = $projects->whereNotNull('moco_id')->pluck('moco_id')->toArray();
        
        // Batch-Load MOCO-Daten für alle Projekte auf einmal
        if (!empty($mocoProjectIds)) {
            $mocoService = app(MocoService::class);
            foreach ($mocoProjectIds as $mocoId) {
                $teamMembers = $mocoService->getProjectTeam($mocoId);
                if ($teamMembers) {
                    $projectTeams[$mocoId] = $teamMembers;
                }
            }
        }

        $assignmentCounts = Assignment::select('project_id', DB::raw('COUNT(*) as total_assignments'))
            ->whereIn('project_id', $projects->pluck('id'))
            ->groupBy('project_id')
            ->pluck('total_assignments', 'project_id');

        $overrideCounts = ProjectAssignmentOverride::select('project_id', DB::raw('COUNT(*) as total_overrides'))
            ->whereIn('project_id', $projects->pluck('id'))
            ->groupBy('project_id')
            ->pluck('total_overrides', 'project_id');

        return view('projects.index', compact('projects', 'projectTeams', 'assignmentCounts', 'overrideCounts'));
    }

    public function reorderAssignments(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'order' => 'required|array',
            'order.*.type' => 'required|string|in:assignment,override',
            'order.*.id' => 'required|integer',
            'order.*.employee_id' => 'nullable|integer|exists:employees,id',
            'bars' => 'nullable|array',
            'bars.*.type' => 'required_with:bars|string|in:assignment,override',
            'bars.*.id' => 'required_with:bars|integer',
            'bars.*.start_date' => 'nullable|date',
            'bars.*.end_date' => 'nullable|date|after_or_equal:bars.*.start_date',
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['order'] as $index => $item) {
                if ($item['type'] === 'assignment') {
                    Assignment::where('id', $item['id'])
                        ->where('project_id', $data['project_id'])
                        ->update(['display_order' => $index]);
                } else {
                    ProjectAssignmentOverride::where('id', $item['id'])
                        ->where('project_id', $data['project_id'])
                        ->update(['display_order' => $index]);
                }
            }

            if (!empty($data['bars'])) {
                foreach ($data['bars'] as $bar) {
                    if ($bar['type'] === 'assignment') {
                        Assignment::where('id', $bar['id'])
                            ->where('project_id', $data['project_id'])
                            ->update([
                                'start_date' => $bar['start_date'],
                                'end_date' => $bar['end_date'],
                            ]);
                    } else {
                        ProjectAssignmentOverride::where('id', $bar['id'])
                            ->where('project_id', $data['project_id'])
                            ->update([
                                'start_date' => $bar['start_date'],
                                'end_date' => $bar['end_date'],
                            ]);
                    }
                }
            }
        });

        return response()->json(['status' => 'ok']);
    }

    public function repositionAssignment(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'source_index' => 'required|integer|min:0',
            'target_index' => 'required|integer|min:0',
        ]);

        if ($data['source_index'] === $data['target_index']) {
            return response()->json(['status' => 'ok']);
        }

        $projectId = $data['project_id'];
        $sourceIndex = $data['source_index'];
        $targetIndex = $data['target_index'];

        $rows = collect();

        $assignments = Assignment::select('id', 'display_order', 'employee_id')
            ->where('project_id', $projectId)
            ->orderBy('display_order')
            ->get()
            ->map(function ($assignment) {
                return [
                    'type' => 'assignment',
                    'id' => $assignment->id,
                    'display_order' => $assignment->display_order,
                ];
            });

        $overrides = ProjectAssignmentOverride::select('id', 'display_order', 'employee_id')
            ->where('project_id', $projectId)
            ->orderBy('display_order')
            ->get()
            ->map(function ($override) {
                return [
                    'type' => 'override',
                    'id' => $override->id,
                    'display_order' => $override->display_order,
                ];
            });

        $rows = $assignments->merge($overrides)->sortBy('display_order')->values();

        if ($sourceIndex < 0 || $sourceIndex >= $rows->count()) {
            throw ValidationException::withMessages([
                'source_index' => __('Ungültiger Ausgangsindex.'),
            ]);
        }

        if ($targetIndex < 0 || $targetIndex >= $rows->count()) {
            throw ValidationException::withMessages([
                'target_index' => __('Ungültiger Zielindex.'),
            ]);
        }

        $movingRow = $rows->pull($sourceIndex);
        $rows->splice($targetIndex, 0, [$movingRow]);

        DB::transaction(function () use ($rows, $projectId) {
            foreach ($rows as $index => $row) {
                if ($row['type'] === 'assignment') {
                    Assignment::where('id', $row['id'])
                        ->where('project_id', $projectId)
                        ->update(['display_order' => $index]);
                } else {
                    ProjectAssignmentOverride::where('id', $row['id'])
                        ->where('project_id', $projectId)
                        ->update(['display_order' => $index]);
                }
            }
        });

        return response()->json(['status' => 'ok']);
    }

    public function resizeAssignment(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string|in:assignment,override',
            'id' => 'required|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($data['type'] === 'assignment') {
            $assignment = Assignment::findOrFail($data['id']);
            $assignment->update([
                'start_date' => $data['start_date'] ?? $assignment->start_date,
                'end_date' => $data['end_date'] ?? $assignment->end_date,
            ]);
        } else {
            $override = ProjectAssignmentOverride::findOrFail($data['id']);
            $override->update([
                'start_date' => $data['start_date'] ?? $override->start_date,
                'end_date' => $data['end_date'] ?? $override->end_date,
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Synchronisiert die Projekt-Status aus MOCO-Daten
     * Wird manuell aufgerufen oder über Cron-Job
     */
    public function syncProjectStatuses()
    {
        $projects = Project::whereNotNull('moco_id')
            ->where('name', '!=', 'Aufträge auf Zuruf')
            ->get();
        $mocoService = app(MocoService::class);
        $updated = 0;
        
        foreach ($projects as $project) {
            try {
                $mocoData = $mocoService->getProject($project->moco_id);
                if ($mocoData) {
                    $newStatus = $this->calculateStatusFromMocoData($mocoData);
                    
                    // Immer aktualisieren basierend auf finish_date
                    if ($project->status !== $newStatus) {
                        $project->update(['status' => $newStatus]);
                        $updated++;
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Fehler beim Synchronisieren des Projekt-Status für Projekt {$project->id}: " . $e->getMessage());
            }
        }
        
        return response()->json([
            'message' => "Status-Synchronisation abgeschlossen. {$updated} Projekte aktualisiert.",
            'updated_count' => $updated,
            'total_projects' => $projects->count()
        ]);
    }
    
    /**
     * Berechnet den Status basierend auf MOCO-Daten
     * Ausschließlich nach finish_date orientieren
     */
    private function calculateStatusFromMocoData($mocoData)
    {
        // Status ausschließlich nach finish_date aus MOCO orientieren
        if (isset($mocoData['finish_date']) && $mocoData['finish_date'] !== null) {
            $finishDate = \Carbon\Carbon::parse($mocoData['finish_date']);
            // finish_date < heute = Abgeschlossen, sonst In Bearbeitung
            return $finishDate->isPast() ? 'completed' : 'active';
        } 
        // finish_date null: Status "In Bearbeitung"
        else {
            return 'active'; // Immer "In Bearbeitung" wenn finish_date null
        }
    }

    public function create()
    {
        $employees = Employee::where('is_active', true)->get();
        return view('projects.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:100',
            'description' => 'nullable',
            'status' => 'required|in:planning,active,completed',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'estimated_hours' => 'nullable|integer|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'progress' => 'nullable|integer|min:0|max:100',
            'responsible_id' => 'nullable|exists:employees,id'
        ]);

        Project::create($validated);
        return redirect('/projects')->with('success', 'Projekt erfolgreich angelegt');
    }

    public function edit(Project $project)
    {
        $employees = Employee::where('is_active', true)->get();
        return view('projects.edit', compact('project', 'employees'));
    }

    public function importForm()
    {
        return view('projects.import');
    }

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
                    // Find responsible employee by name
                    $responsibleId = null;
                    if (isset($data[8]) && !empty($data[8])) {
                        $responsibleName = $data[8];
                        $responsible = Employee::whereRaw("CONCAT(first_name, ' ', last_name) = ?", [$responsibleName])->first();
                        if ($responsible) {
                            $responsibleId = $responsible->id;
                        }
                    }

                    Project::create([
                        'name' => $data[0],
                        'description' => $data[1] ?? null,
                        'status' => $data[2] ?? 'planning',
                        'start_date' => $data[3] ? Carbon::createFromFormat('d.m.Y', $data[3]) : null,
                        'end_date' => $data[4] ? Carbon::createFromFormat('d.m.Y', $data[4]) : null,
                        'progress' => (int)($data[5] ?? 0),
                        'estimated_hours' => $data[6] ? (int)$data[6] : null,
                        'hourly_rate' => $data[7] ? (float)$data[7] : null,
                        'responsible_id' => $responsibleId,
                    ]);
                    $imported++;
                }
            } catch (Exception $e) {
                $errors[] = "Zeile " . ($imported + 1) . ": " . $e->getMessage();
            }
        }
        
        fclose($handle);
        
        $message = "Erfolgreich {$imported} Projekte importiert.";
        if (!empty($errors)) {
            $message .= " Fehler: " . implode(', ', $errors);
        }
        
        return redirect()->route('projects.index')->with('success', $message);
    }

    public function export()
    {
        $projects = Project::withCount('assignments')->get();

        $filename = 'projekte-uebersicht-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($projects) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM für Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header
            fputcsv($file, ['Projekt', 'Beschreibung', 'Status', 'Startdatum', 'Enddatum', 'Fortschritt (%)', 'Geschätzte Stunden', 'Stundensatz (€)', 'Verantwortlicher', 'Zuweisungen'], ';');

            // Daten
            foreach ($projects as $project) {
                fputcsv($file, [
                    $project->name,
                    $project->description ?? '',
                    ucfirst($project->status),
                    Carbon::parse($project->start_date)->format('d.m.Y'),
                    $project->end_date ? Carbon::parse($project->end_date)->format('d.m.Y') : '',
                    $project->progress ?? 0,
                    $project->estimated_hours ?? '',
                    $project->hourly_rate ?? '',
                    $project->responsible ? $project->responsible->first_name . ' ' . $project->responsible->last_name : '',
                    $project->assignments_count
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|max:100',
            'description' => 'nullable',
            'status' => 'required|in:planning,active,completed',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'estimated_hours' => 'nullable|integer|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'progress' => 'nullable|integer|min:0|max:100',
            'responsible_id' => 'nullable|exists:employees,id'
        ]);

        $project->update($validated);
        return redirect('/projects')->with('success', 'Projekt erfolgreich aktualisiert');
    }

    public function destroy(Project $project)
    {
        if ($project->assignments()->count() > 0) {
            return back()->with('error', 'Projekt kann nicht gelöscht werden - es gibt noch Zuweisungen');
        }

        $project->delete();
        return redirect('/projects')->with('success', 'Projekt erfolgreich gelöscht');
    }
}
