<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Services\Projects\ProjectProgressService;
use App\Services\Projects\BudgetAnalysisService;
use App\Services\Projects\TeamAnalysisService;
use App\Services\Projects\TimelineAnalysisService;
use App\Services\Projects\BottleneckAnalyzer;
use App\Services\MocoService;
use Illuminate\Support\Facades\Log;



final class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $projects = Project::query()
            ->with(['assignments.employee', 'timeEntries', 'responsible'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('identifier', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        // Service-Instanzen für Berechnungen
        $progressService = new ProjectProgressService();
        $budgetService = new BudgetAnalysisService();
        $teamService = new TeamAnalysisService();

        // Für jedes Projekt die berechneten Daten hinzufügen
        $projects->getCollection()->transform(function ($project) use ($progressService, $budgetService, $teamService) {
            // Fortschritt berechnen
            $progressData = $progressService->cachedDetails($project, 60);
            
            // Budget-Daten berechnen
            $budgetData = $budgetService->cached($project, 60);
            
            // Team-Daten berechnen
            $teamData = $teamService->summary($project);
            
            // Berechnete Werte dem Projekt hinzufügen
            $project->calculated_progress = $progressData['automatic'];
            $project->actual_hours = $progressData['total_hours_worked'];
            $project->actual_cost = $budgetData['actual_cost'];
            $project->budget_utilization = $budgetData['budget_utilization'];
            $project->remaining_budget = $budgetData['remaining_budget'];
            $project->team_members_count = count($teamData['members']);
            $project->team_utilization = $teamData['team_utilization'];
            
            return $project;
        });

        // Statistiken für die View
        $totalCount = Project::count();
        $activeCount = Project::where('status', 'active')->count();
        $planningCount = Project::where('status', 'planning')->count();
        $completedCount = Project::where('status', 'completed')->count();

        return view('projects.index', compact('projects', 'q', 'totalCount', 'activeCount', 'planningCount', 'completedCount'));
    }

    public function create(): View
    {
        return view('projects.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $project = Project::create($data);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Project created.');
    }

    public function show(
        Project $project,
        ProjectProgressService $progress,
        BudgetAnalysisService $budget,
        TeamAnalysisService $team,
        TimelineAnalysisService $timeline,
        BottleneckAnalyzer $bottlenecks,
        MocoService $mocoService
    ): View {
        $progressDetails = $progress->cachedDetails($project, 60);
        $budgetDetails   = $budget->cached($project, 60);
        $teamSummary     = $team->summary($project);
        $timelineSummary = $timeline->cached($project, 60);
        $bn              = $bottlenecks->analyze($project, $teamSummary, $budgetDetails, $timelineSummary, $progressDetails);
        
        // MOCO-Daten abrufen, falls moco_id vorhanden
        $mocoData = null;
        if ($project->moco_id) {
            try {
                $mocoData = $mocoService->getProjectComprehensive($project->moco_id);
            } catch (\Exception $e) {
                // Log error but don't break the page
                Log::warning("MOCO data fetch failed for project {$project->id}: " . $e->getMessage());
            }
        }
    
        return view('projects.show', [
            'project'     => $project,
            'progress'    => $progressDetails,
            'budget'      => $budgetDetails,
            'team'        => $teamSummary,
            'timeline'    => $timelineSummary,
            'bottlenecks' => $bn,
            'mocoData'    => $mocoData,
        ]);
    }
    
    

    public function edit(Project $project): View
    {
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $data = $this->validated($request, $project->id);

        $project->update($data);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Project updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('status', 'Project deleted.');
    }

    public function export()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="projects.csv"',
        ];

        $callback = function () {
            $out = fopen('php://output', 'w');
            fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM
            fputcsv($out, [
                'id','name','description','status','identifier',
                'start_date','end_date','estimated_hours','hourly_rate','progress',
                'billable','budget','moco_id','created_at','updated_at',
            ]);

            Project::query()
                ->orderBy('id')
                ->chunk(500, function ($rows) use ($out) {
                    foreach ($rows as $p) {
                        fputcsv($out, [
                            $p->id,
                            $p->name,
                            $p->description,
                            $p->status,
                            $p->identifier,
                            (string) $p->start_date,
                            (string) $p->end_date,
                            $p->estimated_hours,
                            $p->hourly_rate,
                            $p->progress,
                            (int) ($p->billable ?? 0),
                            $p->budget,
                            $p->moco_id,
                            optional($p->created_at)->toDateTimeString(),
                            optional($p->updated_at)->toDateTimeString(),
                        ]);
                    }
                });

            fclose($out);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function importForm(): View
    {
        return view('projects.import');
    }

    public function import(Request $request): RedirectResponse
    {
        $v = Validator::make($request->all(), [
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);
        $v->validate();

        $path = $request->file('file')->store('imports');

        $handle = fopen(Storage::path($path), 'r');
        if ($handle === false) {
            return back()->withErrors(['file' => 'File could not be opened.']);
        }

        $firstBytes = fread($handle, 3);
        if ($firstBytes !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
            fseek($handle, 0);
        }

        $header = fgetcsv($handle);
        $map = $this->headerMap($header);

        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $data = $this->rowToProjectData($row, $map);

            $project = null;
            if (!empty($data['id'])) {
                $project = Project::find((int) $data['id']);
            }
            if (!$project && !empty($data['identifier'])) {
                $project = Project::where('identifier', $data['identifier'])->first();
            }

            if ($project) {
                $project->update($data);
            } else {
                Project::create($data);
            }
            $count++;
        }

        fclose($handle);

        return redirect()
            ->route('projects.index')
            ->with('status', "Imported {$count} projects.");
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $identifierRule = 'nullable|string|max:100';
        if ($ignoreId) {
            $identifierRule .= '|unique:projects,identifier,' . $ignoreId;
        } else {
            $identifierRule .= '|unique:projects,identifier';
        }

        return $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'status'          => ['nullable', 'string', 'max:100'],
            'identifier'      => [$identifierRule],
            'start_date'      => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date', 'after_or_equal:start_date'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'hourly_rate'     => ['nullable', 'numeric', 'min:0'],
            'progress'        => ['nullable', 'numeric', 'min:0', 'max:100'],
            'billable'        => ['nullable', 'boolean'],
            'budget'          => ['nullable', 'numeric', 'min:0'],
            'moco_id'         => ['nullable', 'integer'],
            'responsible_id'  => ['nullable', 'integer'],
        ]);
    }

    private function headerMap(?array $header): array
    {
        $map = [];
        if (!$header) {
            return $map;
        }
        foreach ($header as $i => $col) {
            $key = strtolower(trim((string) $col));
            $map[$key] = $i;
        }
        return $map;
    }

    private function rowToProjectData(array $row, array $map): array
    {
        $get = function (string $key, $default = null) use ($row, $map) {
            if (!array_key_exists($key, $map)) {
                return $default;
            }
            $v = $row[$map[$key]] ?? $default;
            return is_string($v) ? trim($v) : $v;
        };

        return [
            'id'              => $get('id') !== null ? (int) $get('id') : null,
            'name'            => $get('name', ''),
            'description'     => $get('description'),
            'status'          => $get('status'),
            'identifier'      => $get('identifier'),
            'start_date'      => $get('start_date') ?: null,
            'end_date'        => $get('end_date') ?: null,
            'estimated_hours' => $get('estimated_hours') !== null ? (float) $get('estimated_hours') : null,
            'hourly_rate'     => $get('hourly_rate') !== null ? (float) $get('hourly_rate') : null,
            'progress'        => $get('progress') !== null ? (float) $get('progress') : null,
            'billable'        => $get('billable') !== null ? (bool) $get('billable') : null,
            'budget'          => $get('budget') !== null ? (float) $get('budget') : null,
            'moco_id'         => $get('moco_id') !== null ? (int) $get('moco_id') : null,
            'responsible_id'  => $get('responsible_id') !== null ? (int) $get('responsible_id') : null,
        ];
    }
}
