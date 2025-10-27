<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Employee;
use App\Models\Assignment;
use App\Models\ProjectAssignmentOverride;
use App\Models\GanttFilterSet;
use App\Services\MocoService;
use App\Services\GanttDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GanttController extends Controller
{
    /**
     * Helper to cache MOCO responses safely.
     */
    protected function rememberMoco(string $key, int $minutes, \Closure $callback)
    {
        if (Cache::has($key)) {
            return Cache::get($key);
        }

        try {
            $value = $callback();

            $isEmpty = false;
            if (is_array($value)) {
                $isEmpty = empty($value);
            } elseif ($value instanceof \Countable) {
                $isEmpty = $value->count() === 0;
            }

            if ($isEmpty) {
                // Behalte vorhandene Daten, falls welche existieren
                if (Cache::has($key)) {
                    return Cache::get($key);
                }
                return $value;
            }

            Cache::put($key, $value, now()->addMinutes($minutes));
            return $value;
        } catch (\Throwable $e) {
            Log::warning('MOCO cache fallback [' . $key . ']: ' . $e->getMessage());
            return Cache::get($key, []);
        }
    }

    public function index()
    {
        // Lade gespeicherte Filter-Einstellungen aus der Session
        $filters = Session::get('gantt_filters', [
            'status' => '',
            'sort' => '',
            'employee' => '',
            'timeframe' => '',
            'custom_date_from' => '',
            'custom_date_to' => '',
            'search' => '',
            'zoom' => '12m',
        ]);

        $viewMode = request()->query('view', 'projects');

        $zoomKey = $this->normalizeZoom($filters['zoom'] ?? null);
        $zoomOptions = $this->getZoomOptions();
        $currentZoom = isset($zoomOptions[$zoomKey]) ? $zoomKey : '12m';

        // Mitarbeiteransicht bevorzugt Wochen-Zoom
        if ($viewMode === 'employees' && str_ends_with($currentZoom, 'm')) {
            $currentZoom = '12w';
        }

        $zoomConfig = $zoomOptions[$currentZoom] ?? $zoomOptions['12m'];
        $timelineUnit = $zoomConfig['unit'];
        $columnWidth = $zoomConfig['column_width'];
        $periodCount = $zoomConfig['count'];
        $customRange = ($filters['timeframe'] ?? '') === 'custom' && !empty($filters['custom_date_from']) && !empty($filters['custom_date_to']);

        // Custom-Range entfällt, da der alte Filter entfernt wurde

        // Lade nur lokale Projekte (wie in der Projekt-Verwaltung)
        $query = Project::with(['assignments.employee']);

        // Wende Filter an
        if (!empty($filters['employee'])) {
            $employeeId = (int)$filters['employee'];
            $mocoProjectIds = [];
            try {
                // Map auf MOCO-User-ID
                $emp = Employee::find($employeeId);
                if ($emp && !empty($emp->moco_id)) {
                    /** @var MocoService $moco */
                    $moco = app(MocoService::class);
                    // Cache MOCO-Userprojekte für kurze Zeit, um Performance zu verbessern
                    $cacheKey = 'moco:user_projects:' . (int)$emp->moco_id;
                    $userProjects = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($moco, $emp) {
                        return $moco->getUserProjects((int)$emp->moco_id);
                    });
                    // Sammle MOCO Projekt-IDs
                    foreach ($userProjects as $mp) {
                        if (isset($mp['id'])) { $mocoProjectIds[] = (int)$mp['id']; }
                    }
                }
            } catch (\Throwable $t) {
                Log::warning('MOCO user projects fetch failed: ' . $t->getMessage());
            }

            $query->where(function ($q) use ($employeeId, $mocoProjectIds) {
                // 1) Lokale Zuweisungen
                $q->whereHas('assignments', function($qa) use ($employeeId) {
                    $qa->where('employee_id', $employeeId);
                })
                // 2) Verantwortlicher des Projekts
                ->orWhere('responsible_id', $employeeId)
                // 3) MOCO: Projekte, in denen der User per Contract zugewiesen ist (Mapping via projects.moco_id)
                ->orWhere(function($qb) use ($mocoProjectIds) {
                    if (!empty($mocoProjectIds)) {
                        $qb->whereIn('moco_id', $mocoProjectIds);
                    }
                });
            });
        }

        if (!empty($filters['search'])) {
            $searchTerm = strtolower($filters['search']);
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%'])
                  ->orWhereRaw('LOWER(COALESCE(description, "")) LIKE ?', ['%' . $searchTerm . '%']);
            });
        }

        // Wenn ein Mitarbeiter gefiltert wird, ignorieren wir bewusst jeden Zeitrahmen,
        // um alle Projekte dieses Mitarbeiters zu zeigen (laut Anforderung).
        if (!empty($filters['timeframe']) && empty($filters['employee'])) {
            $now = now();
            switch ($filters['timeframe']) {
                case 'current':
                    $query->where(function($q) use ($now) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>', $now);
                    });
                    break;
                case 'future':
                    $query->where('start_date', '>', $now);
                    break;
                case 'past':
                    $query->where('end_date', '<=', $now);
                    break;
                case 'this-month':
                    $query->where(function($q) use ($now) {
                        $q->where('start_date', '<=', $now->endOfMonth())
                          ->where(function($q2) use ($now) {
                              $q2->whereNull('end_date')
                                 ->orWhere('end_date', '>=', $now->startOfMonth());
                          });
                    });
                    break;
                case 'this-quarter':
                    $quarterStart = $now->startOfQuarter();
                    $quarterEnd = $now->endOfQuarter();
                    $query->where(function($q) use ($quarterStart, $quarterEnd) {
                        $q->where('start_date', '<=', $quarterEnd)
                          ->where(function($q2) use ($quarterStart) {
                              $q2->whereNull('end_date')
                                 ->orWhere('end_date', '>=', $quarterStart);
                          });
                    });
                    break;
                case 'custom':
                    if (!empty($filters['custom_date_from'])) {
                        $query->where(function($q) use ($filters) {
                            $q->where('start_date', '<=', $filters['custom_date_to'] ?? '9999-12-31')
                              ->where(function($q2) use ($filters) {
                                  $q2->whereNull('end_date')
                                     ->orWhere('end_date', '>=', $filters['custom_date_from']);
                              });
                        });
                    }
                    if (!empty($filters['custom_date_to'])) {
                        $query->where(function($q) use ($filters) {
                            $q->where('start_date', '<=', $filters['custom_date_to'])
                              ->where(function($q2) use ($filters) {
                                  $q2->whereNull('end_date')
                                     ->orWhere('end_date', '>=', $filters['custom_date_from'] ?? '1900-01-01');
                              });
                        });
                    }
                    break;
            }
        }

        // Wende Sortierung an
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'name-asc':
                    $query->orderBy('name');
                    break;
                case 'name-desc':
                    $query->orderBy('name', 'desc');
                    break;
                case 'date-start-asc':
                    $query->orderBy('start_date');
                    break;
                case 'date-start-desc':
                    $query->orderBy('start_date', 'desc');
                    break;
                case 'date-end-asc':
                    $query->orderBy('end_date');
                    break;
                case 'date-end-desc':
                    $query->orderBy('end_date', 'desc');
                    break;
            }
        } else {
            $query->orderBy('start_date');
        }

        $projects = $query->get();
        
        // Always put "Gantt-Testprojekt" at the top for easy access
        $projects = $projects->sortBy(function($project) {
            return $project->name === 'Gantt-Testprojekt' ? 0 : 1;
        })->values();
        
        // ==================== PERFORMANCE OPTIMIERUNG: EAGER LOADING ====================
        
        $projectMap = $projects->keyBy('id');
        $employeeIds = [];

        // 1. Alle Assignments für die geladenen Projekte auf einmal holen
        $allAssignments = \App\Models\Assignment::whereIn('project_id', $projectMap->keys())
            ->with('employee') // Lade auch gleich die Mitarbeiter-Daten mit
            ->orderBy('display_order')
            ->orderBy('start_date')
            ->get()
            ->groupBy('project_id');

        // Sammle alle relevanten Mitarbeiter-IDs aus den Assignments
        foreach ($allAssignments as $assignments) {
            foreach ($assignments as $assignment) {
                if ($assignment->employee_id) {
                    $employeeIds[] = $assignment->employee_id;
                }
            }
        }
        $employeeIds = array_unique($employeeIds);

        // 2. Alle Abwesenheiten für die relevanten Mitarbeiter auf einmal holen
        $allAbsences = \App\Models\Absence::whereIn('employee_id', $employeeIds)
            ->get()
            ->groupBy('employee_id');
            
        // 3. Abwesenheiten pro Projekt zuordnen und zählen
        $projectAbsences = [];
        $projectAbsenceDetails = [];

        foreach ($projects as $project) {
            $projectStart = $project->start_date ? Carbon::parse($project->start_date) : ($project->moco_created_at ? Carbon::parse($project->moco_created_at) : null);
            $projectEnd = $project->end_date ? Carbon::parse($project->end_date) : now()->addMonths(23);
            
            if (!$projectStart) continue;

            $absencesInProject = collect();
            $teamIds = $allAssignments->get($project->id, collect())->pluck('employee_id')->unique()->toArray();
            
            foreach ($teamIds as $teamId) {
                foreach ($allAbsences->get($teamId, collect()) as $absence) {
                    $absenceStart = Carbon::parse($absence->start_date);
                    $absenceEnd = Carbon::parse($absence->end_date);

                    // Prüfe auf Überlappung
                    if ($absenceStart->lte($projectEnd) && $absenceEnd->gte($projectStart)) {
                        $absencesInProject->push($absence);
                    }
                }
            }

            if ($absencesInProject->isNotEmpty()) {
                $projectAbsences[$project->id] = $absencesInProject->count();
                $projectAbsenceDetails[$project->id] = $absencesInProject;
            }
        }

        // ==================== BERECHNUNG DER PROJEKT-METRIKEN ====================
        $ganttDataService = new GanttDataService();
        $projectMetrics = $ganttDataService->calculateProjectMetrics($projects, $allAssignments, $projectAbsenceDetails);

        $projectTimelineBounds = [];
        foreach ($projects as $project) {
            $metrics = $projectMetrics[$project->id] ?? null;

            $boundStart = null;
            if ($metrics && !empty($metrics['startDate'])) {
                $boundStart = $metrics['startDate']->copy()->startOfDay();
            } elseif ($project->start_date) {
                $boundStart = Carbon::parse($project->start_date)->startOfDay();
            } elseif ($project->moco_created_at) {
                $boundStart = Carbon::parse($project->moco_created_at)->startOfDay();
            } else {
                $boundStart = Carbon::now()->startOfMonth();
            }

            $boundEnd = null;
            if ($metrics && !empty($metrics['endDate'])) {
                $boundEnd = $metrics['endDate']->copy()->endOfDay();
            } elseif ($project->end_date) {
                $boundEnd = Carbon::parse($project->end_date)->endOfDay();
            } else {
                $boundEnd = Carbon::now()->addMonths(12)->endOfMonth();
            }

            if ($boundEnd->lt($boundStart)) {
                $boundEnd = $boundStart->copy();
            }

            $projectTimelineBounds[$project->id] = [
                'start' => $boundStart,
                'end' => $boundEnd,
            ];
        }

        $overrideAssignments = ProjectAssignmentOverride::with(['employee', 'project'])
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('start_date')
            ->get();
        $overrideAssignmentsByEmployee = $overrideAssignments->groupBy('employee_id');

        // ==================== TIMELINE-PERIODEN VORBEREITEN ====================
        $timelineMonths = [];
        if ($timelineUnit === 'month') {
            if ($customRange) {
                $startPeriod = Carbon::parse($filters['custom_date_from'])->startOfMonth();
            } else {
                $half = (int)floor($periodCount / 2);
                $startPeriod = Carbon::now()->startOfMonth()->subMonths($half);
            }

            for ($i = 0; $i < $periodCount; $i++) {
                $periodStart = $startPeriod->copy()->addMonths($i);
                $periodEnd = $periodStart->copy()->endOfMonth();
                $timelineMonths[] = [
                    'index' => $i,
                    'label' => $periodStart->format('M Y'),
                    'start' => $periodStart->copy()->startOfDay(),
                    'end' => $periodEnd->copy()->endOfDay(),
                    'is_current' => $periodStart->isSameMonth(Carbon::now()),
                ];
            }
        } else {
            if ($customRange) {
                $startPeriod = Carbon::parse($filters['custom_date_from'])->startOfWeek(Carbon::MONDAY);
            } else {
                $half = (int)floor($periodCount / 2);
                $startPeriod = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeeks($half);
            }

            for ($i = 0; $i < $periodCount; $i++) {
                $periodStart = $startPeriod->copy()->addWeeks($i);
                $periodEnd = $periodStart->copy()->endOfWeek(Carbon::SUNDAY);
                $timelineMonths[] = [
                    'index' => $i,
                    'label' => 'KW ' . $periodStart->isoWeek . ' ' . $periodStart->format('Y'),
                    'start' => $periodStart->copy()->startOfDay(),
                    'end' => $periodEnd->copy()->endOfDay(),
                    'is_current' => $periodStart->isSameWeek(Carbon::now(), Carbon::MONDAY),
                ];
            }
        }

        $timelineStart = $timelineMonths[0]['start']->copy();
        $lastPeriod = $timelineMonths[count($timelineMonths) - 1];
        $timelineEnd = $lastPeriod['end']->copy();
        $totalTimelineDays = max(1, $timelineStart->diffInDays($timelineEnd) + 1);

        // ==================== MITARBEITER-ZEITACHSE VORBEREITEN ====================
        $employees = Employee::with(['assignments.project'])
            ->orderBy('timeline_order')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Load absences for all employees (for employee view)
        $employeeAbsences = \App\Models\Absence::whereIn('employee_id', $employees->pluck('id')->toArray())
            ->get()
            ->groupBy('employee_id');

        $timelineByEmployee = collect();

        foreach ($employees as $employee) {
            $employeeAssignments = collect();
            $seenProjects = [];
            $capacity = (float) ($employee->weekly_capacity ?? 0);
            $totalWeeklyLoad = 0.0;

            foreach ($employee->assignments as $assignment) {
                if (!$assignment->project) {
                    continue;
                }

                $project = $assignment->project;
                $weeklyHours = $assignment->weekly_hours !== null ? (float) $assignment->weekly_hours : null;

                $assignmentStart = null;
                if ($assignment->start_date) {
                    $assignmentStart = Carbon::parse($assignment->start_date)->startOfDay();
                } elseif ($project->start_date) {
                    $assignmentStart = Carbon::parse($project->start_date)->startOfDay();
                } elseif ($project->moco_created_at) {
                    $assignmentStart = Carbon::parse($project->moco_created_at)->startOfDay();
                }

                if (!$assignmentStart) {
                    continue;
                }

                $assignmentEnd = null;
                if ($assignment->end_date) {
                    $assignmentEnd = Carbon::parse($assignment->end_date)->endOfDay();
                } elseif ($project->end_date) {
                    $assignmentEnd = Carbon::parse($project->end_date)->endOfDay();
                }

                if (!$assignmentEnd || $assignmentEnd->lt($assignmentStart)) {
                    $assignmentEnd = now()->copy()->endOfDay();
                }

                $key = $project->moco_id ? 'moco_' . $project->moco_id : 'local_' . $project->id;
                $seenProjects[$key] = true;

                if ($weeklyHours !== null) {
                    $totalWeeklyLoad += $weeklyHours;
                }

                $utilizationRatio = ($capacity > 0 && $weeklyHours !== null) ? $weeklyHours / $capacity : null;
                $isOverCapacity = $utilizationRatio !== null && $utilizationRatio > 1.0;

                $employeeAssignments->push([
                    'project_id' => $project->id,
                    'project_name' => $project->name,
                    'project_moco_id' => $project->moco_id,
                    'project_identifier' => $project->identifier ?? null,
                    'project_status' => $project->status,
                    'project_progress' => $project->progress ?? 0,
                    'project_estimated_hours' => $project->estimated_hours ?? null,
                    'project_hourly_rate' => $project->hourly_rate ?? null,
                    'project_budget' => null,
                    'weekly_hours' => $weeklyHours,
                    'start' => $assignmentStart,
                    'end' => $assignmentEnd,
                    'source' => 'local',
                    'utilization_ratio' => $utilizationRatio,
                    'is_over_capacity' => $isOverCapacity,
                    'assignment_id' => $assignment->id,
                    'task_name' => $assignment->task_name,
                    'task_description' => $assignment->task_description,
                ]);
            }

            if ($employee->moco_id) {
                try {
                    /** @var MocoService $mocoService */
                    $mocoService = app(MocoService::class);
                    $cacheKey = 'moco:user_projects:' . (int) $employee->moco_id;
                    $mocoProjects = $this->rememberMoco($cacheKey, 10, function () use ($mocoService, $employee) {
                        return $mocoService->getUserProjects($employee->moco_id);
                    });
                    if (!is_array($mocoProjects) || empty($mocoProjects)) {
                        $mocoProjects = $this->rememberMoco($cacheKey . ':fallback', 1, function () use ($mocoService, $employee) {
                            return $mocoService->getUserProjects($employee->moco_id);
                        });
                        $mocoProjects = is_array($mocoProjects) ? $mocoProjects : [];
                    }

                    foreach ($mocoProjects as $projectData) {
                        $projectId = $projectData['id'] ?? null;
                        $key = $projectId ? 'moco_' . $projectId : null;
                        if ($key && isset($seenProjects[$key])) {
                            continue;
                        }

                        $projectStart = null;
                        if (!empty($projectData['start_date'])) {
                            $projectStart = Carbon::parse($projectData['start_date'])->startOfDay();
                        } elseif (!empty($projectData['created_at'])) {
                            $projectStart = Carbon::parse($projectData['created_at'])->startOfDay();
                        }

                        if (!$projectStart) {
                            continue;
                        }

                        $projectEnd = null;
                        if (!empty($projectData['finish_date'])) {
                            $projectEnd = Carbon::parse($projectData['finish_date'])->endOfDay();
                        }

                        if (!$projectEnd || $projectEnd->lt($projectStart)) {
                            $projectEnd = now()->copy()->endOfDay();
                        }

                        $contractHours = null;
                        if (!empty($projectData['contracts']) && is_array($projectData['contracts'])) {
                            foreach ($projectData['contracts'] as $contract) {
                                if (($contract['user_id'] ?? null) == $employee->moco_id) {
                                    if (!empty($contract['hours_per_week'])) {
                                        $contractHours = $contract['hours_per_week'];
                                    } elseif (!empty($contract['hours_per_day'])) {
                                        $contractHours = $contract['hours_per_day'] * 5;
                                    }
                                    break;
                                }
                            }
                        }

                        if ($key) {
                            $seenProjects[$key] = true;
                        }

                        if ($contractHours !== null) {
                            $totalWeeklyLoad += $contractHours;
                        }

                        $utilizationRatio = ($capacity > 0 && $contractHours !== null) ? $contractHours / $capacity : null;
                        $isOverCapacity = $utilizationRatio !== null && $utilizationRatio > 1.0;

                        $employeeAssignments->push([
                            'project_id' => null,
                            'project_name' => $projectData['name'] ?? 'Unbenanntes Projekt',
                            'project_moco_id' => $projectId,
                            'project_identifier' => $projectData['identifier'] ?? null,
                            'project_status' => $projectData['status'] ?? null,
                            'project_progress' => $projectData['progress'] ?? ($projectData['completion'] ?? 0),
                            'project_estimated_hours' => $projectData['planned_hours'] ?? $projectData['budget_hours'] ?? null,
                            'project_hourly_rate' => $projectData['hourly_rate'] ?? null,
                            'project_budget' => $projectData['budget'] ?? null,
                            'weekly_hours' => $contractHours,
                            'start' => $projectStart,
                            'end' => $projectEnd,
                            'source' => 'moco',
                            'utilization_ratio' => $utilizationRatio,
                            'is_over_capacity' => $isOverCapacity,
                            'assignment_id' => null,
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::warning('MOCO user projects for employee failed: ' . $e->getMessage());
                }
            }

            $employeeAssignments = $employeeAssignments->filter(function ($assignment) use ($timelineStart, $timelineEnd) {
                $start = $assignment['start'];
                $end = $assignment['end'];
                return $end->gte($timelineStart) && $start->lte($timelineEnd);
            })->sortBy(function ($assignment) {
                return $assignment['start']->timestamp;
            })->values();

            if ($employeeAssignments->isEmpty()) {
                $timelineByEmployee->push([
                    'employee' => $employee,
                    'assignments' => collect(),
                    'summary' => [
                        'capacity' => $capacity,
                        'total_weekly_load' => 0,
                        'overload_ratio' => null,
                        'project_count' => 0,
                    ],
                    'span' => [
                        'raw_start' => $timelineStart,
                        'raw_end' => $timelineEnd,
                        'start' => $timelineStart,
                        'end' => $timelineStart,
                    ],
                    'projects' => collect(),
                ]);
                continue;
            }

            $firstAssignment = $employeeAssignments->first();
            $lastAssignment = $employeeAssignments->sortByDesc(function ($assignment) {
                return $assignment['end']->timestamp;
            })->first();

            $employeeRawStart = $firstAssignment['start']->copy();
            $employeeRawEnd = $lastAssignment['end']->copy();

            $employeeSpanStart = $employeeRawStart->copy()->max($timelineStart);
            $employeeSpanEnd = $employeeRawEnd->copy()->min($timelineEnd);
            if ($employeeSpanEnd->lt($employeeSpanStart)) {
                $employeeSpanEnd = $employeeSpanStart->copy();
            }

            $projectGroups = $employeeAssignments->groupBy(function ($assignment) {
                if (!empty($assignment['project_moco_id'])) {
                    return 'moco_' . $assignment['project_moco_id'];
                }

                if (!empty($assignment['project_id'])) {
                    return 'local_' . $assignment['project_id'];
                }

                return 'misc_' . md5(($assignment['project_name'] ?? 'unknown') . ($assignment['source'] ?? 'unknown'));
            });

            $projectRows = $projectGroups->map(function ($group) use ($employeeSpanStart, $employeeSpanEnd, $capacity, $timelineStart, $timelineEnd) {
                $sortedByStart = $group->sortBy(function ($assignment) {
                    return $assignment['start']->timestamp;
                })->values();

                $sortedByEnd = $group->sortByDesc(function ($assignment) {
                    return $assignment['end']->timestamp;
                })->values();

                $rawStart = $sortedByStart->first()['start']->copy();
                $rawEnd = $sortedByEnd->first()['end']->copy();
                $assignmentIds = $group->pluck('assignment_id')->filter()->values();
                $overrideIds = $group->pluck('override_id')->filter()->values();

                $clampedStart = $rawStart->copy()->max($employeeSpanStart)->max($timelineStart);
                $clampedEnd = $rawEnd->copy()->min($employeeSpanEnd)->min($timelineEnd);
                if ($clampedEnd->lt($clampedStart)) {
                    $clampedEnd = $clampedStart->copy();
                }

                $totalWeeklyHours = $group->sum(function ($assignment) {
                    return $assignment['weekly_hours'] ?? 0;
                });

                $first = $sortedByStart->first();
                $sources = $group->pluck('source')->filter()->unique()->values();
                $primaryActivity = $group->pluck('primary_activity')->filter()->first();

                return [
                    'project_id' => $first['project_id'] ?? null,
                    'project_moco_id' => $first['project_moco_id'] ?? null,
                    'project_name' => $first['project_name'] ?? 'Unbekanntes Projekt',
                    'project_status' => $first['project_status'] ?? null,
                    'project_progress' => $first['project_progress'] ?? 0,
                    'project_identifier' => $first['project_identifier'] ?? null,
                    'project_budget' => $first['project_budget'] ?? null,
                    'project_hourly_rate' => $first['project_hourly_rate'] ?? null,
                    'project_estimated_hours' => $first['project_estimated_hours'] ?? null,
                    'raw_start' => $rawStart,
                    'raw_end' => $rawEnd,
                    'start' => $clampedStart,
                    'end' => $clampedEnd,
                    'weekly_hours' => round($totalWeeklyHours, 2),
                    'utilization_ratio' => $capacity > 0 ? ($totalWeeklyHours / $capacity) : null,
                    'is_over_capacity' => $capacity > 0 ? ($totalWeeklyHours > $capacity) : ($totalWeeklyHours > 0),
                    'sources' => $sources,
                    'primary_activity' => $primaryActivity,
                    'assignment_ids' => $assignmentIds,
                    'override_ids' => $overrideIds,
                ];
            })->filter(function ($project) {
                return $project['start'] && $project['end'];
            })->sortBy(function ($project) {
                return $project['start']->timestamp;
            })->values();

            $overloadRatio = ($capacity > 0) ? ($totalWeeklyLoad / $capacity) : null;

            // Calculate time-based utilization metrics with absences
            $employeeAbsenceData = $employeeAbsences->get($employee->id, collect());
            $utilizationMetrics = $this->calculateTimeBasedUtilization(
                $employeeAssignments,
                $capacity,
                $employeeAbsenceData
            );

            $timelineByEmployee->push([
                'employee' => $employee,
                'assignments' => $employeeAssignments,
                'summary' => [
                    'capacity' => $capacity,
                    'total_weekly_load' => round($totalWeeklyLoad, 2),
                    'overload_ratio' => $overloadRatio,
                    'project_count' => $projectRows->count(),
                    'peak_utilization_percent' => $utilizationMetrics['peak_utilization_percent'] ?? 0,
                    'average_utilization_percent' => $utilizationMetrics['average_utilization_percent'] ?? 0,
                    'has_absences' => $employeeAbsenceData->isNotEmpty(),
                ],
                'span' => [
                    'raw_start' => $employeeRawStart,
                    'raw_end' => $employeeRawEnd,
                    'start' => $employeeSpanStart,
                    'end' => $employeeSpanEnd,
                ],
                'projects' => $projectRows,
            ]);
        }

        // ==================== ABWESENHEITEN PRO PROJEKT (ALT) ====================
        /*
        // Berechne für jedes Projekt, wie viele Abwesenheiten es betreffen
        $projectAbsences = [];
        foreach ($projects as $project) {
            $teamIds = $project->assignments->pluck('employee_id')->toArray();
            
            if (empty($teamIds)) {
                continue;
            }
            
            $projectStart = $project->start_date ?: $project->moco_created_at;
            $projectEnd = $project->end_date ?: now()->addMonths(23);
            
            if (!$projectStart || !$projectEnd) {
                continue;
            }
            
            // Zähle Abwesenheiten, die mit dem Projektzeitraum überlappen
            $absenceCount = DB::table('absences')
                ->whereIn('employee_id', $teamIds)
                ->where('start_date', '<=', $projectEnd)
                ->where('end_date', '>=', $projectStart)
                ->count();
            
            if ($absenceCount > 0) {
                $projectAbsences[$project->id] = $absenceCount;
            }
        }
        */

        // Lade Team-Mitglieder für alle Projekte effizient (MOCO + lokale Daten)
        $projectTeams = [];
        $mocoProjectIds = $projects->whereNotNull('moco_id')->pluck('moco_id')->toArray();
        
        // Batch-Load MOCO-Daten für alle Projekte auf einmal
        if (!empty($mocoProjectIds)) {
            $mocoService = app(MocoService::class);
            foreach ($mocoProjectIds as $mocoId) {
                $cacheKey = 'moco:project_team:' . (int) $mocoId;
                $teamMembers = $this->rememberMoco($cacheKey, 15, function () use ($mocoService, $mocoId) {
                    return $mocoService->getProjectTeam($mocoId);
                });
                if ($teamMembers) {
                    $projectTeams[$mocoId] = $teamMembers;
                }
            }
        }

        $availableEmployees = Employee::orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);

        $activeFilters = [];
        if (!empty($filters['status'])) {
            $activeFilters[] = [
                'label' => $filters['status'] === 'in_bearbeitung' ? 'In Bearbeitung' : 'Abgeschlossen',
                'field' => 'status',
            ];
        }
        if (!empty($filters['timeframe'])) {
            $labels = [
                'current' => 'Aktive Projekte',
                'future' => 'Zukünftig',
                'past' => 'Abgeschlossen',
                'this-month' => 'Dieser Monat',
                'this-quarter' => 'Dieses Quartal',
                'custom' => 'Benutzerdefiniert',
            ];
            $activeFilters[] = [
                'label' => $labels[$filters['timeframe']] ?? $filters['timeframe'],
                'field' => 'timeframe',
            ];
        }
        if (!empty($filters['employee'])) {
            $emp = $availableEmployees->firstWhere('id', (int) ($filters['employee'] ?? 0));
            if ($emp) {
                $activeFilters[] = [
                    'label' => $emp->first_name . ' ' . $emp->last_name,
                    'field' => 'employee',
                ];
            }
        }
        if (!empty($filters['search'])) {
            $activeFilters[] = [
                'label' => 'Suche: ' . $filters['search'],
                'field' => 'search',
            ];
        }
        if ($customRange) {
            $activeFilters[] = [
                'label' => 'Zeitraum: ' . Carbon::parse($filters['custom_date_from'])->format('d.m.Y') . ' - ' . Carbon::parse($filters['custom_date_to'])->format('d.m.Y'),
                'field' => 'custom_range',
            ];
        }

        $projectAssignmentsGrouped = collect();

        foreach ($projects as $project) {
            $projectAssignmentsGrouped[$project->id] = [
                'summary' => [
                    'total_weekly_load' => 0,
                    'member_count' => 0,
                    'team_names' => collect(),
                    'team_names_count' => 0,
                ],
                'assignments' => collect(),
                'team_members' => collect(),
            ];

            $detailContracts = method_exists($project, 'moco_contracts') && $project->moco_contracts ? collect($project->moco_contracts) : collect();
            if ($detailContracts->isNotEmpty()) {
                $projectAssignmentsGrouped[$project->id]['team_members'] = $detailContracts;
                $projectAssignmentsGrouped[$project->id]['summary']['team_names'] = $detailContracts->pluck('name')->filter()->values();
                $projectAssignmentsGrouped[$project->id]['summary']['team_names_count'] = $detailContracts->count();
            } elseif ($project->moco_id && isset($projectTeams[$project->moco_id])) {
                $teamMembers = collect($projectTeams[$project->moco_id]);
                $projectAssignmentsGrouped[$project->id]['team_members'] = $teamMembers;
                $projectAssignmentsGrouped[$project->id]['summary']['team_names'] = $teamMembers->pluck('name')->filter()->values();
                $projectAssignmentsGrouped[$project->id]['summary']['team_names_count'] = $teamMembers->count();
            }
        }

        $projectAssignmentsGroupedArray = $projectAssignmentsGrouped->map(fn($item) => $item)->toArray();

        foreach ($timelineByEmployee as $entry) {
            /** @var \App\Models\Employee $employeeModel */
            $employeeModel = $entry['employee'];
            $employeeAssignments = $entry['assignments'];
            $capacity = $entry['summary']['capacity'];

            $employeeOverrides = $overrideAssignmentsByEmployee->get($employeeModel->id) ?? collect();

            foreach ($employeeAssignments as $assignment) {
                $projectId = $assignment['project_id'];
                if (!$projectId || !isset($projectAssignmentsGroupedArray[$projectId])) {
                    continue;
                }

                $project = $projectMap->get($projectId);
                if (!$project) {
                    continue;
                }

                $assignmentIdCollection = collect($assignment['assignment_ids'] ?? []);
                $overrideIdCollection = collect($assignment['override_ids'] ?? []);

                $user = $projectAssignmentsGroupedArray[$projectId]['team_members']->firstWhere('user_id', $employeeModel->moco_id);
                $employeeName = $user['name'] ?? (trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')) ?: 'Unbekannt');

                $bounds = $projectTimelineBounds[$projectId] ?? ['start' => $timelineStart, 'end' => $timelineEnd];
                $projectStart = $bounds['start'];
                $projectEnd = $bounds['end'];

                $startDate = !empty($assignment['start_date']) ? Carbon::parse($assignment['start_date'])->startOfDay() : $projectStart;
                $endDate = !empty($assignment['finish_date']) ? Carbon::parse($assignment['finish_date'])->endOfDay() : $projectEnd;

                $clampedStart = $assignment['start']->copy()->max($projectStart);
                $clampedEnd = $assignment['end']->copy()->min($projectEnd);
                if ($clampedEnd->lt($clampedStart)) {
                    $clampedEnd = $clampedStart->copy();
                }

                $projectAssignmentsGroupedArray[$projectId]['assignments']->push([
                    'employee_id' => $user['id'] ?? null,
                    'employee_name' => $employeeName,
                    'raw_start' => $startDate,
                    'raw_end' => $endDate,
                    'project_start' => $projectStart,
                    'project_end' => $projectEnd,
                    'start' => $clampedStart,
                    'end' => $clampedEnd,
                    'weekly_hours' => $assignment['weekly_hours'],
                    'capacity' => $capacity,
                    'utilization_ratio' => $assignment['utilization_ratio'],
                    'is_over_capacity' => $assignment['is_over_capacity'],
                    'source' => $assignment['source'],
                    'primary_activity' => $assignment['primary_activity'] ?? null,
                    'assignment_id' => $assignmentIdCollection->first(),
                    'assignment_ids' => $assignmentIdCollection->values()->all(),
                    'override_id' => $overrideIdCollection->first(),
                    'override_ids' => $overrideIdCollection->values()->all(),
                    'display_order' => $assignment['display_order'] ?? null,
                    'can_edit' => $assignmentIdCollection->isNotEmpty() || $overrideIdCollection->isNotEmpty(),
                    'dnd_type' => $assignmentIdCollection->isNotEmpty() ? 'assignment' : ($overrideIdCollection->isNotEmpty() ? 'override' : 'moco'),
                    'task_name' => $assignment['task_name'] ?? null,
                    'task_description' => $assignment['task_description'] ?? null,
                ]);

                $projectAssignmentsGroupedArray[$projectId]['summary']['total_weekly_load'] += $assignment['weekly_hours'] ?? 0;
                $projectAssignmentsGroupedArray[$projectId]['summary']['member_count'] += 1;
            }

            foreach ($employeeOverrides as $override) {
                $project = $override->project_id ? $projectMap->get($override->project_id) : null;
                if (!$project) {
                    continue;
                }

                $projectId = $project->id;
                if (!isset($projectAssignmentsGroupedArray[$projectId])) {
                    continue;
                }

                $bounds = $projectTimelineBounds[$projectId] ?? ['start' => $timelineStart, 'end' => $timelineEnd];
                $overrideStart = Carbon::parse($override->start_date)->startOfDay();
                $overrideEnd = $override->end_date ? Carbon::parse($override->end_date)->endOfDay() : $overrideStart->copy()->endOfDay();
                $clampedStart = $overrideStart->copy()->max($bounds['start']);
                $clampedEnd = $overrideEnd->copy()->min($bounds['end']);
                if ($clampedEnd->lt($clampedStart)) {
                    $clampedEnd = $clampedStart->copy();
                }

                $projectAssignmentsGroupedArray[$projectId]['assignments'] = collect($projectAssignmentsGroupedArray[$projectId]['assignments'])
                    ->reject(function ($assignment) use ($employeeModel) {
                        return ($assignment['employee_id'] ?? null) === $employeeModel->id && ($assignment['source'] ?? '') !== 'override';
                    })
                    ->values();

                $projectAssignmentsGroupedArray[$projectId]['assignments']->push([
                    'employee_id' => $employeeModel->id,
                    'employee_name' => $employeeModel->first_name . ' ' . $employeeModel->last_name,
                    'raw_start' => $overrideStart,
                    'raw_end' => $overrideEnd,
                    'project_start' => $bounds['start'],
                    'project_end' => $bounds['end'],
                    'start' => $clampedStart,
                    'end' => $clampedEnd,
                    'weekly_hours' => $override->weekly_hours,
                    'capacity' => $employeeModel->weekly_capacity ?? 0,
                    'utilization_ratio' => null,
                    'is_over_capacity' => false,
                    'source' => 'override',
                    'primary_activity' => $override->activity,
                    'is_override' => true,
                    'assignment_ids' => [],
                    'override_id' => $override->id,
                    'override_ids' => [$override->id],
                    'override_label' => $override->source_label,
                    'assignment_id' => null,
                    'display_order' => $override->display_order ?? null,
                    'can_edit' => true,
                    'dnd_type' => 'override',
                ]);

                $projectAssignmentsGroupedArray[$projectId]['summary']['total_weekly_load'] += $override->weekly_hours ?? 0;
                $projectAssignmentsGroupedArray[$projectId]['summary']['member_count'] = collect($projectAssignmentsGroupedArray[$projectId]['assignments'])
                    ->pluck('employee_id')
                    ->filter()
                    ->unique()
                    ->count();
            }
        }

        $mocoAssignmentsByProject = collect();
        if (!empty($mocoProjectIds)) {
            $mocoService = app(MocoService::class);
            foreach ($mocoProjectIds as $mocoId) {
                $cacheKey = 'moco:project_assignments:' . (int) $mocoId;
                $assignments = $this->rememberMoco($cacheKey, 15, function () use ($mocoService, $mocoId) {
                    return $mocoService->getProjectAssignmentsCached($mocoId);
                });
                $assignments = collect($assignments);
                if ($assignments->isNotEmpty()) {
                    $mocoAssignmentsByProject[$mocoId] = $assignments;
                }
            }
        }

        foreach ($projectMap as $projectId => $project) {
            $mocoId = $project->moco_id;

            $teamMembers = collect($projectAssignmentsGroupedArray[$projectId]['team_members'] ?? []);
            if ($teamMembers->isEmpty() && $mocoId) {
                $fetched = collect($this->rememberMoco('moco:project_team:' . (int) $mocoId, 15, function () use ($mocoService, $mocoId) {
                    return $mocoService->getProjectTeam($mocoId);
                }) ?? []);
                if ($fetched->isEmpty()) {
                    $projectDetail = $this->rememberMoco('moco:project_detail:' . (int) $mocoId, 15, function () use ($mocoService, $mocoId) {
                        return $mocoService->getProject($mocoId);
                    });
                    if ($projectDetail && isset($projectDetail['contracts'])) {
                        $fetched = collect($projectDetail['contracts'])->map(function ($contract) {
                            $firstName = $contract['firstname'] ?? ($contract['user']['firstname'] ?? '');
                            $lastName = $contract['lastname'] ?? ($contract['user']['lastname'] ?? '');
                            return [
                                'user_id' => $contract['user_id'] ?? ($contract['user']['id'] ?? null),
                                'name' => trim($firstName . ' ' . $lastName) ?: ($contract['user']['display_name'] ?? 'Unbekannt'),
                                'role' => $contract['role'] ?? null,
                                'hours_per_week' => $contract['hours_per_week'] ?? null,
                                'start_date' => $contract['start_date'] ?? null,
                                'end_date' => $contract['finish_date'] ?? $contract['end_date'] ?? null,
                            ];
                        });
                    }
                }
                $teamMembers = $fetched;
                $projectAssignmentsGroupedArray[$projectId]['team_members'] = $teamMembers;
            }

            $existingUserIds = $projectAssignmentsGroupedArray[$projectId]['assignments']->pluck('employee_id')->filter()->map(fn($id) => (int)$id)->unique();

            if ($mocoId && !empty($mocoAssignmentsByProject[$mocoId])) {
                foreach ($mocoAssignmentsByProject[$mocoId] as $assignment) {
                    $user = $assignment['user'] ?? null;
                    if (!$user) {
                        continue;
                    }

                    $userId = $user['id'] ?? null;
                    if ($userId && $existingUserIds->contains((int)$userId)) {
                        continue;
                    }

                    $employeeName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')) ?: ($user['display_name'] ?? 'Unbekannt');
                    $weeklyHours = $assignment['hours_per_week'] ?? (($assignment['hours_total'] ?? 0) / max(1, $assignment['duration'] ?? 1));

                    $bounds = $projectTimelineBounds[$projectId] ?? ['start' => $timelineStart, 'end' => $timelineEnd];
                    $projectStart = $bounds['start'];
                    $projectEnd = $bounds['end'];

                    $startDate = !empty($assignment['start_date'])
                        ? Carbon::parse($assignment['start_date'])->startOfDay()
                        : $projectStart;

                    $endDate = !empty($assignment['finish_date'])
                        ? Carbon::parse($assignment['finish_date'])->endOfDay()
                        : $projectEnd;

                    $clampedStart = $startDate->copy()->max($projectStart);
                    $clampedEnd = $endDate->copy()->min($projectEnd);
                    if ($clampedEnd->lt($clampedStart)) {
                        $clampedEnd = $clampedStart->copy();
                    }

                    $projectAssign = [
                        'employee_id' => $userId,
                        'employee_name' => $employeeName,
                        'raw_start' => $startDate,
                        'raw_end' => $endDate,
                        'project_start' => $projectStart,
                        'project_end' => $projectEnd,
                        'start' => $clampedStart,
                        'end' => $clampedEnd,
                        'weekly_hours' => $weeklyHours,
                        'capacity' => $assignment['hours_per_week'] ?? 0,
                        'utilization_ratio' => null,
                        'is_over_capacity' => false,
                        'source' => 'moco_contract',
                        'primary_activity' => $assignment['role'] ?? ($assignment['description'] ?? null),
                        'assignment_id' => null,
                        'override_id' => null,
                        'display_order' => null,
                        'can_edit' => false,
                        'dnd_type' => 'moco',
                    ];

                    $projectAssignmentsGroupedArray[$projectId]['assignments']->push($projectAssign);

                    if ($userId) {
                        $existingUserIds->push((int)$userId);
                    }
                    $projectAssignmentsGroupedArray[$projectId]['summary']['total_weekly_load'] += $weeklyHours ?? 0;
                    $projectAssignmentsGroupedArray[$projectId]['summary']['member_count'] += 1;
                }
            }

            if ($teamMembers->isNotEmpty()) {
                foreach ($teamMembers as $member) {
                    $userId = $member['user_id'] ?? ($member['user']['id'] ?? null);
                    if ($userId && $existingUserIds->contains((int)$userId)) {
                        continue;
                    }

                    $employeeName = $member['name'] ?? (trim(($member['user']['firstname'] ?? '') . ' ' . ($member['user']['lastname'] ?? '')) ?: 'Unbekannt');

                    $bounds = $projectTimelineBounds[$projectId] ?? ['start' => $timelineStart, 'end' => $timelineEnd];
                    $projectStart = $bounds['start'];
                    $projectEnd = $bounds['end'];

                    $start = $projectStart;
                    $end = $projectEnd;

                    $projectAssignmentsGroupedArray[$projectId]['assignments']->push([
                        'employee_id' => $userId,
                        'employee_name' => $employeeName,
                        'raw_start' => $start,
                        'raw_end' => $end,
                        'project_start' => $project->start_date ? Carbon::parse($project->start_date)->startOfDay() : null,
                        'project_end' => $project->end_date ? Carbon::parse($project->end_date)->endOfDay() : null,
                        'start' => $start,
                        'end' => $end,
                        'weekly_hours' => $member['hours_per_week'] ?? null,
                        'capacity' => $member['hours_per_week'] ?? 0,
                        'utilization_ratio' => null,
                        'is_over_capacity' => false,
                        'source' => 'moco_team',
                        'primary_activity' => $member['role'] ?? null,
                        'assignment_id' => null,
                        'override_id' => null,
                    ]);

                    if ($userId) {
                        $existingUserIds->push((int)$userId);
                    }
                    $projectAssignmentsGroupedArray[$projectId]['summary']['member_count'] += 1;
                }
            }
        }

        $timeEntryFallback = \App\Models\TimeEntry::with(['employee'])
            ->whereIn('project_id', $projects->pluck('id'))
            ->get()
            ->groupBy('project_id');

        foreach ($projectAssignmentsGroupedArray as $projectId => $data) {
            if ($data['assignments']->isNotEmpty()) {
                continue;
            }

            $entries = $timeEntryFallback->get($projectId, collect());
            if ($entries->isEmpty()) {
                continue;
            }

            $employeeGroups = $entries->groupBy('employee_id');
            foreach ($employeeGroups as $employeeId => $employeeEntries) {
                $employee = $employeeEntries->first()->employee;
                if (!$employee) {
                    continue;
                }

                $totalHours = $employeeEntries->sum('hours');
                $primaryActivity = $employeeEntries->groupBy(function ($entry) {
                    return $entry->description ?: 'Allgemein';
                })->sortByDesc(function ($group) {
                    return $group->sum('hours');
                })->keys()->first();

                $startDate = $employeeEntries->min('date')->copy()->startOfDay();
                $endDate = $employeeEntries->max('date')->copy()->endOfDay();

                $project = $projectMap->get($projectId);
                $projectStart = $project && $project->start_date ? Carbon::parse($project->start_date)->startOfDay() : $timelineStart;
                $projectEnd = $project && $project->end_date ? Carbon::parse($project->end_date)->endOfDay() : $timelineEnd;
                if ($projectEnd->lt($projectStart)) {
                    $projectEnd = $projectStart->copy();
                }

                $clampedStart = $startDate->copy()->max($projectStart);
                $clampedEnd = $endDate->copy()->min($projectEnd);
                if ($clampedEnd->lt($clampedStart)) {
                    $clampedEnd = $clampedStart->copy();
                }

                $projectAssignmentsGroupedArray[$projectId]['assignments']->push([
                    'employee_id' => $employeeId,
                    'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                    'raw_start' => $startDate,
                    'raw_end' => $endDate,
                    'project_start' => $projectStart,
                    'project_end' => $projectEnd,
                    'start' => $clampedStart,
                    'end' => $clampedEnd,
                    'weekly_hours' => $totalHours,
                    'capacity' => $employee->weekly_capacity ?? 0,
                    'utilization_ratio' => null,
                    'is_over_capacity' => false,
                    'source' => 'timeentries',
                    'primary_activity' => $primaryActivity,
                ]);

                $projectAssignmentsGroupedArray[$projectId]['summary']['total_weekly_load'] += $totalHours;
                $projectAssignmentsGroupedArray[$projectId]['summary']['member_count'] += 1;
            }
        }

        $timelineByProject = collect($projectAssignmentsGroupedArray)->map(function ($data) {
            $data['assignments'] = $data['assignments']->sortBy(function ($assignment) {
                return $assignment['start']->timestamp;
            })->values();
            $data['has_assignments'] = $data['assignments']->isNotEmpty();
            return $data;
        });

        return view('gantt.index', compact(
            'projects',
            'projectAbsences',
            'projectAbsenceDetails',
            'allAssignments',
            'projectTeams',
            'projectMetrics',
            'availableEmployees',
            'activeFilters',
            'timelineMonths',
            'currentZoom',
            'timelineUnit',
            'columnWidth',
            'customRange',
            'viewMode',
            'timelineByEmployee',
            'timelineByProject',
            'timelineStart',
            'timelineEnd',
            'totalTimelineDays',
            'employeeAbsences'
        ));
    }

    public function export()
    {
        // Verwende die gleiche Logik wie in index() - nur lokale Projekte
        $projects = Project::withCount('assignments')->get();

        $filename = 'gantt-projekte-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($projects) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM für Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header
            fputcsv($file, ['Projekt', 'Status', 'Startdatum', 'Enddatum', 'Dauer (Tage)', 'Fortschritt (%)', 'Zuweisungen'], ';');

            // Daten
            foreach ($projects as $project) {
                $duration = Carbon::parse($project->start_date)->diffInDays(Carbon::parse($project->end_date)) + 1;

                fputcsv($file, [
                    $project->name,
                    ucfirst($project->status),
                    Carbon::parse($project->start_date)->format('d.m.Y'),
                    Carbon::parse($project->end_date)->format('d.m.Y'),
                    $duration,
                    $project->progress ?? 0,
                    $project->assignments_count
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function filter()
    {
        // Lade alle verfügbaren Mitarbeiter für Verantwortliche-Filter
        $employees = Employee::orderBy('first_name')->get();
        
        // Lade gespeicherte Filter-Sets des aktuellen Benutzers
        $filterSets = GanttFilterSet::forUser(Auth::id())->orderBy('name')->get();
        
        // Lade gespeicherte Filter-Einstellungen aus der Session
        $filters = Session::get('gantt_filters', [
            'status' => '',
            'sort' => '',
            'employee' => '',
            'timeframe' => '',
            'custom_date_from' => '',
            'custom_date_to' => '',
            'search' => '',
            'zoom' => 12,
        ]);

        return view('gantt.filter', compact('employees', 'filters', 'filterSets'));
    }

    public function updateFilter(Request $request)
    {
        // Normalisiere leere Strings auf null, damit 'nullable' greift
        $keysToNormalize = ['status','sort','employee','timeframe','custom_date_from','custom_date_to','search','zoom'];
        $normalized = [];
        foreach ($keysToNormalize as $key) {
            if ($request->has($key)) {
                $val = $request->input($key);
                $normalized[$key] = ($val === '') ? null : $val;
            }
        }
        if (!empty($normalized)) {
            $request->merge($normalized);
        }
        $request->validate([
            'status' => 'nullable|string|in:in_bearbeitung,abgeschlossen',
            'sort' => 'nullable|string|in:name-asc,name-desc,date-start-asc,date-start-desc,date-end-asc,date-end-desc',
            'employee' => 'nullable|integer|exists:employees,id',
            'timeframe' => 'nullable|string|in:current,future,past,this-month,this-quarter,custom',
            'custom_date_from' => 'nullable|date',
            'custom_date_to' => 'nullable|date|after_or_equal:custom_date_from',
            'search' => 'nullable|string|max:100',
            'zoom' => 'nullable|string|in:6w,6m,12m,24m',
        ]);

        $zoom = $this->normalizeZoom($request->input('zoom'));

        // Speichere Filter-Einstellungen in der Session (null -> '')
        Session::put('gantt_filters', [
            'status' => $request->input('status') ?? '',
            'sort' => $request->input('sort') ?? '',
            'employee' => $request->input('employee') ?? '',
            'timeframe' => $request->input('timeframe') ?? '',
            'custom_date_from' => $request->input('custom_date_from') ?? '',
            'custom_date_to' => $request->input('custom_date_to') ?? '',
            'search' => $request->input('search') ?? '',
            'zoom' => $zoom,
        ]);

        return redirect()->route('gantt.index')->with('success', 'Filter-Einstellungen wurden gespeichert.');
    }

    public function saveFilterSet(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:in_bearbeitung,abgeschlossen',
            'sort' => 'nullable|string|in:name-asc,name-desc,date-start-asc,date-start-desc,date-end-asc,date-end-desc',
            'employee' => 'nullable|integer|exists:employees,id',
            'timeframe' => 'nullable|string|in:current,future,past,this-month,this-quarter,custom',
            'custom_date_from' => 'nullable|date',
            'custom_date_to' => 'nullable|date|after_or_equal:custom_date_from',
            'search' => 'nullable|string|max:100',
            'is_default' => 'nullable|boolean'
        ]);

        // Prüfe ob bereits ein Filter-Set mit diesem Namen existiert
        $existingSet = GanttFilterSet::forUser(Auth::id())
            ->where('name', $request->name)
            ->first();

        if ($existingSet) {
            return back()->withErrors(['name' => 'Ein Filter-Set mit diesem Namen existiert bereits.']);
        }

        // Wenn dieses Set als Standard markiert wird, entferne Standard-Flag von anderen Sets
        if ($request->boolean('is_default')) {
            GanttFilterSet::forUser(Auth::id())->update(['is_default' => false]);
        }

        // Erstelle neues Filter-Set
        GanttFilterSet::create([
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => Auth::id(),
            'filters' => [
                'status' => $request->input('status', ''),
                'sort' => $request->input('sort', ''),
                'employee' => $request->input('employee', ''),
                'timeframe' => $request->input('timeframe', ''),
                'custom_date_from' => $request->input('custom_date_from', ''),
                'custom_date_to' => $request->input('custom_date_to', ''),
                'search' => $request->input('search', ''),
                'zoom' => $this->normalizeZoom($request->input('zoom')),
            ],
            'is_default' => $request->boolean('is_default')
        ]);

        return redirect()->route('gantt.filter')->with('success', 'Filter-Set "' . $request->name . '" wurde gespeichert.');
    }

    public function resetFilters(Request $request)
    {
        // Leere alle gespeicherten Filter aus der Session
        Session::put('gantt_filters', [
            'status' => '',
            'sort' => '',
            'employee' => '',
            'timeframe' => '',
            'custom_date_from' => '',
            'custom_date_to' => '',
            'search' => '',
            'zoom' => '12m',
        ]);

        // Bleibe in der Filtermaske nach dem Zurücksetzen
        return redirect()->route('gantt.filter')->with('success', 'Filter wurden zurückgesetzt.');
    }

    public function loadFilterSet($id)
    {
        $filterSet = GanttFilterSet::forUser(Auth::id())->findOrFail($id);
        
        // Lade Filter-Einstellungen in die Session
        $filters = $filterSet->filters;
        $filters['zoom'] = $this->normalizeZoom($filters['zoom'] ?? null);
        Session::put('gantt_filters', $filters);
        
        return redirect()->route('gantt.index')->with('success', 'Filter-Set "' . $filterSet->name . '" wurde geladen.');
    }

    public function deleteFilterSet($id)
    {
        $filterSet = GanttFilterSet::forUser(Auth::id())->findOrFail($id);
        $name = $filterSet->name;
        
        $filterSet->delete();
        
        return redirect()->route('gantt.filter')->with('success', 'Filter-Set "' . $name . '" wurde gelöscht.');
    }

    public function setDefaultFilterSet($id)
    {
        // Entferne Standard-Flag von allen Sets des Benutzers
        GanttFilterSet::forUser(Auth::id())->update(['is_default' => false]);
        
        // Setze neues Standard-Set
        $filterSet = GanttFilterSet::forUser(Auth::id())->findOrFail($id);
        $filterSet->update(['is_default' => true]);
        
        return redirect()->route('gantt.filter')->with('success', 'Filter-Set "' . $filterSet->name . '" wurde als Standard gesetzt.');
    }

    public function refreshMocoCache(Request $request)
    {
        // Optional: gezielt für aktuellen Mitarbeiter-Filter cachen/invalidieren
        $filters = Session::get('gantt_filters', []);
        $cleared = false;

        if (!empty($filters['employee'])) {
            $emp = Employee::find((int)$filters['employee']);
            if ($emp && !empty($emp->moco_id)) {
                $cacheKey = 'moco:user_projects:' . (int)$emp->moco_id;
                Cache::forget($cacheKey);
                $cleared = true;
            }
        }

        // Zusätzlich globale Keys optional löschen (sicher)
        Cache::forget('moco:last_refreshed_at');
        Cache::put('moco:last_refreshed_at', now()->toDateTimeString(), now()->addMinutes(60));

        return redirect()->route('gantt.filter')->with('success', $cleared ? 'MOCO-Cache für den ausgewählten Mitarbeiter wurde aktualisiert.' : 'MOCO-Cache aktualisiert.');
    }

    protected function getZoomOptions(): array
    {
        return [
            '6w' => [
                'label' => '6 Wochen',
                'unit' => 'week',
                'count' => 6,
                'column_width' => 100,
            ],
            '12w' => [
                'label' => '12 Wochen',
                'unit' => 'week',
                'count' => 12,
                'column_width' => 90,
            ],
            '24w' => [
                'label' => '24 Wochen',
                'unit' => 'week',
                'count' => 24,
                'column_width' => 80,
            ],
            '52w' => [
                'label' => '52 Wochen',
                'unit' => 'week',
                'count' => 52,
                'column_width' => 60,
            ],
            '6m' => [
                'label' => '6 Monate',
                'unit' => 'month',
                'count' => 6,
                'column_width' => 160,
            ],
            '12m' => [
                'label' => '12 Monate',
                'unit' => 'month',
                'count' => 12,
                'column_width' => 140,
            ],
            '24m' => [
                'label' => '24 Monate',
                'unit' => 'month',
                'count' => 24,
                'column_width' => 120,
            ],
        ];
    }

    protected function normalizeZoom(?string $zoom): string
    {
        $default = '12m';
        if (!$zoom) {
            return $default;
        }
        $zoom = strtolower(trim($zoom));
        if (isset($this->getZoomOptions()[$zoom])) {
            return $zoom;
        }
        if (is_numeric($zoom)) {
            $zoomKey = $zoom . 'm';
            return isset($this->getZoomOptions()[$zoomKey]) ? $zoomKey : $default;
        }
        return $default;
    }

    public function addEmployeeToProject(Request $request, Project $project)
    {
        $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
        ]);

        $employee = Employee::find($request->employee_id);

        // Check if employee already has tasks in this project
        $existingTasksCount = Assignment::where('project_id', $project->id)
            ->where('employee_id', $request->employee_id)
            ->count();

        if ($existingTasksCount > 0) {
            return redirect()->route('gantt.index')
                ->with('error', 'Dieser Mitarbeiter ist bereits dem Projekt zugewiesen.');
        }

        // Determine next task number for this employee in this project
        $taskNumber = $existingTasksCount + 1;

        // Create new assignment with a default task
        Assignment::create([
            'project_id' => $project->id,
            'employee_id' => $request->employee_id,
            'task_name' => 'Aufgabe ' . $taskNumber,
            'task_description' => 'Aufgabe für ' . $employee->first_name . ' ' . $employee->last_name,
            'start_date' => $project->start_date ?? now(),
            'end_date' => $project->end_date ?? now()->addMonths(1),
            'weekly_hours' => 20,
            'display_order' => Assignment::where('project_id', $project->id)
                ->where('employee_id', $request->employee_id)
                ->max('display_order') ?? 0 + 1,
        ]);

        // Clear any relevant caches
        Cache::forget('moco:user_projects:' . $employee->moco_id);
        
        return redirect()->route('gantt.index')
            ->with('success', 'Mitarbeiter "' . $employee->first_name . ' ' . $employee->last_name . '" wurde dem Projekt zugewiesen.');
    }

    public function addTaskToEmployee(Request $request, Project $project, Employee $employee)
    {
        $request->validate([
            'task_name' => 'required|string|max:255',
            'task_description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'end_date_fixed' => 'nullable|date|after_or_equal:start_date',
            'weekly_hours' => 'nullable|integer|min:1|max:40',
        ]);

        // Determine end date based on which mode was used
        $endDate = $request->end_date ?? $request->end_date_fixed ?? now()->addWeeks(2);

        // Get the next display order
        $maxOrder = Assignment::where('project_id', $project->id)
            ->where('employee_id', $employee->id)
            ->max('display_order');

        // Create new task (assignment)
        Assignment::create([
            'project_id' => $project->id,
            'employee_id' => $employee->id,
            'task_name' => $request->task_name,
            'task_description' => $request->task_description,
            'start_date' => $request->start_date,
            'end_date' => $endDate,
            'weekly_hours' => $request->weekly_hours ?? 20,
            'display_order' => ($maxOrder ?? 0) + 1,
        ]);

        return redirect()->route('gantt.index')
            ->with('success', 'Aufgabe "' . $request->task_name . '" wurde erfolgreich hinzugefügt.');
    }

    /**
     * Remove an employee from a project by deleting all their assignments
     */
    public function removeEmployeeFromProject(Project $project, Employee $employee)
    {
        // Get all assignments for this employee in this project
        $assignments = Assignment::where('project_id', $project->id)
            ->where('employee_id', $employee->id)
            ->get();

        if ($assignments->isEmpty()) {
            return redirect()->route('gantt.index')
                ->with('error', 'Mitarbeiter ist nicht in diesem Projekt zugewiesen.');
        }

        // Delete all assignments
        $deletedCount = Assignment::where('project_id', $project->id)
            ->where('employee_id', $employee->id)
            ->delete();

        // Clear cache
        Cache::forget('moco:user_projects:' . $employee->moco_id);

        return redirect()->route('gantt.index')
            ->with('success', $employee->first_name . ' ' . $employee->last_name . ' wurde aus dem Projekt entfernt (' . $deletedCount . ' Aufgabe(n) gelöscht).');
    }

    /**
     * Get all tasks for an employee in a specific project
     */
    public function getEmployeeTasks(Project $project, Employee $employee)
    {
        $tasks = Assignment::where('project_id', $project->id)
            ->where('employee_id', $employee->id)
            ->orderBy('display_order')
            ->orderBy('start_date')
            ->get();

        return response()->json([
            'success' => true,
            'tasks' => $tasks,
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
            ],
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
            ],
        ]);
    }

    /**
     * Get a single task
     */
    public function getTask(Assignment $assignment)
    {
        return response()->json([
            'success' => true,
            'task' => $assignment,
        ]);
    }

    /**
     * Delete a task (assignment)
     */
    public function deleteTask(Assignment $assignment)
    {
        $taskName = $assignment->task_name;
        $assignment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Aufgabe "' . $taskName . '" wurde gelöscht.',
        ]);
    }

    /**
     * Update a task (assignment)
     */
    public function updateTask(Request $request, Assignment $assignment)
    {
        $request->validate([
            'task_name' => 'required|string|max:255',
            'task_description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'weekly_hours' => 'nullable|integer|min:1|max:40',
        ]);

        $assignment->update([
            'task_name' => $request->task_name,
            'task_description' => $request->task_description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'weekly_hours' => $request->weekly_hours ?? 20,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Aufgabe wurde aktualisiert.',
            'task' => $assignment,
        ]);
    }

    /**
     * Get employee utilization across all projects
     */
    public function getEmployeeUtilization(Employee $employee)
    {
        // Get all assignments for this employee across all projects
        $assignments = Assignment::where('employee_id', $employee->id)
            ->with('project')
            ->orderBy('start_date')
            ->get();

        // Get absences for this employee
        $absences = Absence::where('employee_id', $employee->id)
            ->get();

        // Get unique projects
        $projectIds = $assignments->pluck('project_id')->unique();
        $projectCount = $projectIds->count();

        // Calculate time-based utilization (now with absences)
        $utilizationData = $this->calculateTimeBasedUtilization($assignments, $employee->weekly_capacity ?? 40, $absences);

        // Format tasks with project information
        $tasks = $assignments->map(function ($assignment) {
            return [
                'id' => $assignment->id,
                'task_name' => $assignment->task_name,
                'task_description' => $assignment->task_description,
                'start_date' => $assignment->start_date,
                'end_date' => $assignment->end_date,
                'weekly_hours' => $assignment->weekly_hours,
                'project_id' => $assignment->project_id,
                'project_name' => $assignment->project ? $assignment->project->name : 'Unbekanntes Projekt',
            ];
        });

        return response()->json([
            'success' => true,
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
            ],
            'total_weekly_hours' => $utilizationData['total_hours'],
            'peak_weekly_hours' => $utilizationData['peak_hours'],
            'average_weekly_hours' => $utilizationData['average_hours'],
            'has_overlaps' => $utilizationData['has_overlaps'],
            'overlap_weeks' => $utilizationData['overlap_weeks'],
            'project_count' => $projectCount,
            'tasks' => $tasks,
        ]);
    }

    /**
     * Calculate time-based utilization considering overlaps AND absences
     */
    private function calculateTimeBasedUtilization($assignments, $defaultWeeklyCapacity = 40, $absences = null)
    {
        if ($assignments->isEmpty()) {
            return [
                'total_hours' => 0,
                'peak_hours' => 0,
                'average_hours' => 0,
                'has_overlaps' => false,
                'overlap_weeks' => 0,
                'peak_utilization_percent' => 0,
                'average_utilization_percent' => 0,
            ];
        }

        // Define analysis period (next 6 months from now)
        $analysisStart = Carbon::now()->startOfWeek();
        $analysisEnd = Carbon::now()->addMonths(6)->endOfWeek();

        // Create a map of weeks with their assigned hours
        $weeklyHours = [];
        // Create a map of weeks with their effective capacity (accounting for absences)
        $weeklyCapacity = [];
        
        foreach ($assignments as $assignment) {
            // Handle both arrays and objects
            if (is_array($assignment)) {
                $startDate = $assignment['start_date'] ?? $assignment['start'] ?? null;
                $endDate = $assignment['end_date'] ?? $assignment['end'] ?? null;
                $assignmentWeeklyHours = $assignment['weekly_hours'] ?? 0;
            } else {
                $startDate = $assignment->start_date ?? $assignment->start ?? null;
                $endDate = $assignment->end_date ?? $assignment->end ?? null;
                $assignmentWeeklyHours = $assignment->weekly_hours ?? 0;
            }
            
            if (!$startDate || !$endDate) {
                continue;
            }
            
            $start = ($startDate instanceof Carbon) ? $startDate->copy()->startOfWeek() : Carbon::parse($startDate)->startOfWeek();
            $end = ($endDate instanceof Carbon) ? $endDate->copy()->endOfWeek() : Carbon::parse($endDate)->endOfWeek();
            
            // Only consider assignments within analysis period
            if ($end->lt($analysisStart) || $start->gt($analysisEnd)) {
                continue;
            }
            
            // Clamp to analysis period
            $start = $start->lt($analysisStart) ? $analysisStart->copy() : $start;
            $end = $end->gt($analysisEnd) ? $analysisEnd->copy() : $end;
            
            // Add hours for each week in the assignment period
            $currentWeek = $start->copy();
            while ($currentWeek->lte($end)) {
                $weekKey = $currentWeek->format('Y-W');
                
                if (!isset($weeklyHours[$weekKey])) {
                    $weeklyHours[$weekKey] = 0;
                    $weeklyCapacity[$weekKey] = $defaultWeeklyCapacity; // Start with full capacity
                }
                
                $weeklyHours[$weekKey] += $assignmentWeeklyHours;
                $currentWeek->addWeek();
            }
        }

        // Subtract absence hours from weekly capacity
        if ($absences && $absences->isNotEmpty()) {
            foreach ($absences as $absence) {
                $absenceStart = Carbon::parse($absence->start_date)->startOfWeek();
                $absenceEnd = Carbon::parse($absence->end_date)->endOfWeek();
                
                // Only consider absences within analysis period
                if ($absenceEnd->lt($analysisStart) || $absenceStart->gt($analysisEnd)) {
                    continue;
                }
                
                // Clamp to analysis period
                $absenceStart = $absenceStart->lt($analysisStart) ? $analysisStart->copy() : $absenceStart;
                $absenceEnd = $absenceEnd->gt($analysisEnd) ? $analysisEnd->copy() : $absenceEnd;
                
                // Calculate absence hours for each affected week
                $currentWeek = $absenceStart->copy();
                while ($currentWeek->lte($absenceEnd)) {
                    $weekKey = $currentWeek->format('Y-W');
                    
                    // Initialize capacity if not set
                    if (!isset($weeklyCapacity[$weekKey])) {
                        $weeklyCapacity[$weekKey] = $defaultWeeklyCapacity;
                    }
                    
                    // Calculate how many days of this absence fall in this week
                    $weekStart = $currentWeek->copy()->startOfWeek();
                    $weekEnd = $currentWeek->copy()->endOfWeek();
                    
                    $overlapStart = Carbon::parse($absence->start_date)->max($weekStart);
                    $overlapEnd = Carbon::parse($absence->end_date)->min($weekEnd);
                    
                    if ($overlapStart->lte($overlapEnd)) {
                        // Count business days (Mon-Fri) in overlap period
                        $absenceDays = 0;
                        $checkDay = $overlapStart->copy();
                        while ($checkDay->lte($overlapEnd)) {
                            if ($checkDay->isWeekday()) {
                                $absenceDays++;
                            }
                            $checkDay->addDay();
                        }
                        
                        // Calculate hours lost (assuming 8h per day for 40h week = 5 days)
                        $hoursPerDay = $defaultWeeklyCapacity / 5;
                        $absenceHours = $absenceDays * $hoursPerDay;
                        
                        // Reduce capacity for this week
                        $weeklyCapacity[$weekKey] = max(0, $weeklyCapacity[$weekKey] - $absenceHours);
                    }
                    
                    $currentWeek->addWeek();
                }
            }
        }

        // Calculate statistics
        if (empty($weeklyHours)) {
            return [
                'total_hours' => 0,
                'peak_hours' => 0,
                'average_hours' => 0,
                'has_overlaps' => false,
                'overlap_weeks' => 0,
                'peak_utilization_percent' => 0,
                'average_utilization_percent' => 0,
            ];
        }

        $peakHours = max($weeklyHours);
        $averageHours = round(array_sum($weeklyHours) / count($weeklyHours), 1);
        $totalHours = $assignments->sum('weekly_hours');
        
        // Calculate utilization percentages considering effective capacity
        $utilizationPercentages = [];
        $overlapWeeks = 0;
        
        foreach ($weeklyHours as $weekKey => $hours) {
            $effectiveCapacity = $weeklyCapacity[$weekKey] ?? $weeklyCapacity;
            
            // Edge case: if effective capacity is 0 but hours > 0, set to 999%
            if ($effectiveCapacity <= 0 && $hours > 0) {
                $utilizationPercent = 999;
            } elseif ($effectiveCapacity > 0) {
                $utilizationPercent = round(($hours / $effectiveCapacity) * 100, 1);
            } else {
                $utilizationPercent = 0;
            }
            
            $utilizationPercentages[] = $utilizationPercent;
            
            // Count as overlap if exceeds original standard capacity
            if ($hours > $weeklyCapacity) {
                $overlapWeeks++;
            }
        }
        
        $hasOverlaps = $overlapWeeks > 0;
        $peakUtilizationPercent = !empty($utilizationPercentages) ? max($utilizationPercentages) : 0;
        $averageUtilizationPercent = !empty($utilizationPercentages) 
            ? round(array_sum($utilizationPercentages) / count($utilizationPercentages), 1) 
            : 0;

        return [
            'total_hours' => $totalHours,
            'peak_hours' => $peakHours,
            'average_hours' => $averageHours,
            'has_overlaps' => $hasOverlaps,
            'overlap_weeks' => $overlapWeeks,
            'peak_utilization_percent' => $peakUtilizationPercent,
            'average_utilization_percent' => $averageUtilizationPercent,
        ];
    }
}
