<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Employee;
use App\Models\Assignment;
use App\Models\Absence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GanttController extends Controller
{
    public function index(Request $request)
    {
        // Zeitraum-Parameter aus Request - Standard: nächste 6 Monate
        $dateFrom = $request->get('date_from', now()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->addMonths(6)->format('Y-m-d'));
        
        // Lade alle Projekte (inkl. MOCO-Projekte) mit oder ohne Start-/Enddaten
        $projects = Project::with(['assignments.employee', 'responsible'])
            ->where(function($query) use ($dateFrom, $dateTo) {
                // Projekte mit Zeiträumen im gewählten Bereich
                $query->where(function($q) use ($dateFrom, $dateTo) {
                    $q->whereNotNull('start_date')
                      ->whereNotNull('end_date')
                      ->where(function($subQ) use ($dateFrom, $dateTo) {
                          $subQ->whereBetween('start_date', [$dateFrom, $dateTo])
                               ->orWhereBetween('end_date', [$dateFrom, $dateTo])
                               ->orWhere(function($innerQ) use ($dateFrom, $dateTo) {
                                   $innerQ->where('start_date', '<=', $dateFrom)
                                         ->where('end_date', '>=', $dateTo);
                               });
                      });
                })
                // ODER MOCO-Projekte ohne lokale Zeiträume (zeige alle)
                ->orWhereNotNull('moco_id');
            })
            ->orderBy('start_date')
            ->orderBy('name')
            ->get();

        // Abwesenheits-Analyse für Gantt-Diagramm
        $absenceWarnings = $this->analyzeAbsences($projects);

        // Erweiterte Bottleneck-Analyse
        $bottleneckAnalysis = $this->analyzeAdvancedBottlenecks($projects);

        // Timeline-Ansicht (Tag, Woche, Monat)
        $timelineView = $request->get('view', 'day'); // Standard: Tag
        $timelineData = $this->generateTimelineData($timelineView, $dateFrom, $dateTo);

        return view('gantt.index', compact('projects', 'absenceWarnings', 'bottleneckAnalysis', 'timelineData', 'timelineView', 'dateFrom', 'dateTo'));
    }

    private function analyzeAbsences($projects)
    {
        $absenceWarnings = [];
        $now = now();

        foreach ($projects as $project) {
            $projectAbsences = [];
            
            // Prüfe Abwesenheiten für alle Mitarbeiter des Projekts
            foreach ($project->assignments as $assignment) {
                $employee = $assignment->employee;
                
                // Finde Abwesenheiten während der Projektlaufzeit
                $absences = Absence::where('employee_id', $employee->id)
                    ->where(function($query) use ($project) {
                        $query->whereBetween('start_date', [$project->start_date, $project->end_date])
                              ->orWhereBetween('end_date', [$project->start_date, $project->end_date])
                              ->orWhere(function($q) use ($project) {
                                  $q->where('start_date', '<=', $project->start_date)
                                    ->where('end_date', '>=', $project->end_date);
                              });
                    })
                    ->get();

                if ($absences->count() > 0) {
                    $projectAbsences[] = [
                        'employee' => $employee,
                        'assignment' => $assignment,
                        'absences' => $absences,
                        'total_days' => $absences->sum(function($absence) use ($project) {
                            $start = max(Carbon::parse($absence->start_date), Carbon::parse($project->start_date));
                            $end = min(Carbon::parse($absence->end_date), Carbon::parse($project->end_date));
                            return $start->diffInDays($end) + 1;
                        })
                    ];
                }
            }

            if (!empty($projectAbsences)) {
                $absenceWarnings[] = [
                    'project' => $project,
                    'absences' => $projectAbsences,
                    'total_affected_employees' => count($projectAbsences),
                    'total_absence_days' => collect($projectAbsences)->sum('total_days')
                ];
            }
        }

        return $absenceWarnings;
    }

    /**
     * Erweiterte Bottleneck-Analyse für alle Projekte
     */
    private function analyzeAdvancedBottlenecks($projects)
    {
        $bottlenecks = [];
        $resourceConflicts = [];
        $criticalPaths = [];

        foreach ($projects as $project) {
            $projectAnalysis = $project->analyzeMocoData();
            
            // Projekt-spezifische Bottlenecks
            if ($projectAnalysis['bottlenecks']['total_count'] > 0) {
                $bottlenecks[] = [
                    'project' => $project,
                    'bottlenecks' => $projectAnalysis['bottlenecks']['bottlenecks'],
                    'critical_count' => $projectAnalysis['bottlenecks']['critical_count'],
                    'warning_count' => $projectAnalysis['bottlenecks']['warning_count']
                ];
            }

            // Ressourcen-Konflikte zwischen Projekten
            $teamMembers = $projectAnalysis['team']['team_members'];
            foreach ($teamMembers as $member) {
                if ($member['utilization'] > 100) {
                    $resourceConflicts[] = [
                        'employee' => $member['employee'],
                        'project' => $project,
                        'utilization' => $member['utilization'],
                        'overload' => $member['utilization'] - 100
                    ];
                }
            }

            // Kritische Pfade (überfällige Projekte)
            if ($projectAnalysis['timeline'] && $projectAnalysis['timeline']['is_overdue']) {
                $criticalPaths[] = [
                    'project' => $project,
                    'overdue_days' => abs($projectAnalysis['timeline']['remaining_days']),
                    'budget_utilization' => $projectAnalysis['budget']['budget_utilization']
                ];
            }
        }

        return [
            'project_bottlenecks' => $bottlenecks,
            'resource_conflicts' => $resourceConflicts,
            'critical_paths' => $criticalPaths,
            'total_bottlenecks' => count($bottlenecks),
            'total_conflicts' => count($resourceConflicts),
            'total_critical' => count($criticalPaths)
        ];
    }

    private function generateTimelineData($view, $dateFrom = null, $dateTo = null)
    {
        $startDate = $dateFrom ? Carbon::parse($dateFrom) : now();
        $endDate = $dateTo ? Carbon::parse($dateTo) : now()->addDays(30);
        $timelineData = [];

        switch ($view) {
            case 'day':
                // Timeline basierend auf gewähltem Zeitraum
                $current = $startDate->copy();
                while ($current->lte($endDate)) {
                    $timelineData[] = [
                        'label' => $current->format('d.m'),
                        'full_label' => $current->format('d.m.Y'),
                        'date' => $current->format('Y-m-d'),
                        'is_weekend' => $current->isWeekend(),
                        'is_today' => $current->isToday(),
                        'weekday' => $current->format('D')
                    ];
                    $current->addDay();
                }
                break;

            case 'week':
                // Wochen Timeline basierend auf gewähltem Zeitraum
                $current = $startDate->copy()->startOfWeek();
                while ($current->lte($endDate)) {
                    $weekEnd = $current->copy()->endOfWeek();
                    $timelineData[] = [
                        'label' => 'KW ' . $current->format('W'),
                        'full_label' => 'KW ' . $current->format('W') . ' (' . $current->format('d.m') . ' - ' . $weekEnd->format('d.m') . ')',
                        'start_date' => $current->format('Y-m-d'),
                        'end_date' => $weekEnd->format('Y-m-d'),
                        'is_current_week' => $current->isCurrentWeek()
                    ];
                    $current->addWeek();
                }
                break;

            case 'month':
            default:
                // Monate Timeline basierend auf gewähltem Zeitraum
                $current = $startDate->copy()->startOfMonth();
                while ($current->lte($endDate)) {
                    $timelineData[] = [
                        'label' => $current->format('M Y'),
                        'full_label' => $current->format('F Y'),
                        'month' => $current->format('Y-m'),
                        'is_current_month' => $current->isCurrentMonth()
                    ];
                    $current->addMonth();
                }
                break;
        }

        return $timelineData;
    }

    public function calculateProjectPosition($startDate, $endDate, $timelineView, $timelineData)
    {
        $positions = [];
        
        foreach ($timelineData as $index => $timelineItem) {
            $isInRange = false;
            
            switch ($timelineView) {
                case 'day':
                    $timelineDate = Carbon::parse($timelineItem['date']);
                    $isInRange = $timelineDate->between($startDate, $endDate);
                    break;
                    
                case 'week':
                    $weekStart = Carbon::parse($timelineItem['start_date']);
                    $weekEnd = Carbon::parse($timelineItem['end_date']);
                    $isInRange = $startDate->lte($weekEnd) && $endDate->gte($weekStart);
                    break;
                    
                case 'month':
                default:
                    $monthStart = Carbon::parse($timelineItem['month'] . '-01')->startOfMonth();
                    $monthEnd = $monthStart->copy()->endOfMonth();
                    $isInRange = $startDate->lte($monthEnd) && $endDate->gte($monthStart);
                    break;
            }
            
            $positions[] = [
                'index' => $index,
                'is_in_range' => $isInRange,
                'is_current' => $this->isCurrentPeriod($startDate, $endDate, $timelineItem, $timelineView),
                'is_overdue' => $this->isOverdue($startDate, $endDate, $timelineItem, $timelineView)
            ];
        }
        
        return $positions;
    }
    
    private function isCurrentPeriod($startDate, $endDate, $timelineItem, $timelineView)
    {
        $now = now();
        
        switch ($timelineView) {
            case 'day':
                $timelineDate = Carbon::parse($timelineItem['date']);
                return $timelineDate->isToday() && $now->between($startDate, $endDate);
                
            case 'week':
                return isset($timelineItem['is_current_week']) && $timelineItem['is_current_week'] && $now->between($startDate, $endDate);
                
            case 'month':
            default:
                return isset($timelineItem['is_current_month']) && $timelineItem['is_current_month'] && $now->between($startDate, $endDate);
        }
    }
    
    private function isOverdue($startDate, $endDate, $timelineItem, $timelineView)
    {
        $now = now();
        
        switch ($timelineView) {
            case 'day':
                $timelineDate = Carbon::parse($timelineItem['date']);
                return $timelineDate->gt($now) && $now->gt($endDate);
                
            case 'week':
                $weekStart = Carbon::parse($timelineItem['start_date']);
                return $weekStart->gt($now) && $now->gt($endDate);
                
            case 'month':
            default:
                $monthStart = Carbon::parse($timelineItem['month'] . '-01')->startOfMonth();
                return $monthStart->gt($now) && $now->gt($endDate);
        }
    }

    private function analyzeBottlenecks($projects)
    {
        $bottlenecks = [];
        $now = now();

        foreach ($projects as $project) {
            // Überlastete Mitarbeiter
            $overloadedEmployees = [];
            foreach ($project->assignments as $assignment) {
                $employee = $assignment->employee;
                $totalHours = Assignment::where('employee_id', $employee->id)
                    ->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now)
                    ->sum('weekly_hours');
                
                if ($totalHours > $employee->weekly_capacity) {
                    $overloadedEmployees[] = [
                        'employee' => $employee,
                        'capacity' => $employee->weekly_capacity,
                        'assigned' => $totalHours,
                        'overload' => $totalHours - $employee->weekly_capacity
                    ];
                }
            }

            // Überlappende Projekte
            $overlappingProjects = Project::where('id', '!=', $project->id)
                ->where(function($query) use ($project) {
                    $query->whereBetween('start_date', [$project->start_date, $project->end_date])
                          ->orWhereBetween('end_date', [$project->start_date, $project->end_date])
                          ->orWhere(function($q) use ($project) {
                              $q->where('start_date', '<=', $project->start_date)
                                ->where('end_date', '>=', $project->end_date);
                          });
                })
                ->get();

            // Kritische Zeiträume
            $criticalPeriods = [];
            $projectStart = Carbon::parse($project->start_date);
            $projectEnd = Carbon::parse($project->end_date);
            $daysToStart = $now->diffInDays($projectStart, false);
            
            if ($daysToStart <= 7 && $daysToStart >= 0) {
                $criticalPeriods[] = 'Projekt startet in ' . $daysToStart . ' Tagen';
            }
            
            if ($projectEnd->diffInDays($now) <= 14 && $projectEnd->diffInDays($now) >= 0) {
                $criticalPeriods[] = 'Projekt endet in ' . $projectEnd->diffInDays($now) . ' Tagen';
            }

            if (!empty($overloadedEmployees) || !empty($overlappingProjects) || !empty($criticalPeriods)) {
                $bottlenecks[] = [
                    'project' => $project,
                    'overloaded_employees' => $overloadedEmployees,
                    'overlapping_projects' => $overlappingProjects,
                    'critical_periods' => $criticalPeriods,
                    'risk_level' => $this->calculateRiskLevel($overloadedEmployees, $overlappingProjects, $criticalPeriods)
                ];
            }
        }

        return $bottlenecks;
    }

    private function analyzeRisks($projects)
    {
        $risks = [];
        $now = now();

        foreach ($projects as $project) {
            $projectRisks = [];

            // Abwesenheiten während Projektlaufzeit
            $absences = Absence::whereHas('employee.assignments', function($query) use ($project) {
                $query->where('project_id', $project->id);
            })
            ->where(function($query) use ($project) {
                $query->whereBetween('start_date', [$project->start_date, $project->end_date])
                      ->orWhereBetween('end_date', [$project->start_date, $project->end_date]);
            })
            ->with('employee')
            ->get();

            if ($absences->count() > 0) {
                $projectRisks[] = [
                    'type' => 'absence',
                    'message' => $absences->count() . ' Abwesenheit(en) während Projektlaufzeit',
                    'details' => $absences->map(function($absence) {
                        return $absence->employee->first_name . ' ' . $absence->employee->last_name . 
                               ' (' . $absence->type . ', ' . $absence->start_date . ' - ' . $absence->end_date . ')';
                    })->toArray()
                ];
            }

            // Niedrige Fortschrittsrate
            $projectStart = Carbon::parse($project->start_date);
            $projectEnd = Carbon::parse($project->end_date);
            $totalDays = $projectStart->diffInDays($projectEnd);
            $elapsedDays = $projectStart->diffInDays($now);
            
            if ($elapsedDays > 0 && $project->progress < ($elapsedDays / $totalDays) * 100 - 10) {
                $projectRisks[] = [
                    'type' => 'progress',
                    'message' => 'Fortschritt liegt unter dem erwarteten Wert',
                    'details' => ['Erwartet: ' . round(($elapsedDays / $totalDays) * 100) . '%, Tatsächlich: ' . $project->progress . '%']
                ];
            }

            // Fehlende Verantwortliche
            if (!$project->responsible) {
                $projectRisks[] = [
                    'type' => 'responsibility',
                    'message' => 'Kein Verantwortlicher zugewiesen',
                    'details' => []
                ];
            }

            if (!empty($projectRisks)) {
                $risks[] = [
                    'project' => $project,
                    'risks' => $projectRisks
                ];
            }
        }

        return $risks;
    }

    private function calculateRiskLevel($overloadedEmployees, $overlappingProjects, $criticalPeriods)
    {
        $score = 0;
        
        $score += count($overloadedEmployees) * 3;
        $score += count($overlappingProjects) * 2;
        $score += count($criticalPeriods) * 1;
        
        if ($score >= 8) return 'high';
        if ($score >= 4) return 'medium';
        return 'low';
    }

    public function export()
    {
        $projects = Project::whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->withCount('assignments')
            ->get();

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

    public function bottlenecks()
    {
        $projects = Project::whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->with(['assignments.employee', 'responsible'])
            ->orderBy('start_date')
            ->get();

        // Fokus auf Abwesenheits-Analyse
        $absenceWarnings = $this->analyzeAbsences($projects);

        // Statistiken
        $stats = [
            'total_projects' => $projects->count(),
            'projects_with_absences' => count($absenceWarnings),
            'total_affected_employees' => collect($absenceWarnings)->sum('total_affected_employees'),
            'total_absence_days' => collect($absenceWarnings)->sum('total_absence_days'),
        ];

        return view('gantt.bottlenecks', compact('absenceWarnings', 'stats'));
    }
}
