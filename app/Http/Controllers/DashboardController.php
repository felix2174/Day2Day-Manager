<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // ==================== EXECUTIVE KPIs ====================
        
        // 1. PROJECT STATISTICS
        $totalProjects = DB::table('projects')->count();
        $activeProjects = DB::table('projects')
            ->where(function($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', now());
            })
            ->count();
        $completedProjects = DB::table('projects')
            ->whereNotNull('end_date')
            ->where('end_date', '<', now())
            ->count();
        $planningProjects = DB::table('projects')
            ->where('start_date', '>', now())
            ->count();
        
        // 2. REVENUE & FINANCIAL METRICS
        $activeProjectsData = DB::table('projects')
            ->select('id', 'name', 'estimated_hours', 'hourly_rate', 'progress', 'start_date', 'end_date')
            ->where(function($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', now());
            })
            ->get();
        
        $totalEstimatedRevenue = 0;
        $totalActualRevenue = 0;
        
        foreach ($activeProjectsData as $project) {
            $estimatedRevenue = ($project->estimated_hours ?? 0) * ($project->hourly_rate ?? 0);
            $actualRevenue = $estimatedRevenue * (($project->progress ?? 0) / 100);
            
            $totalEstimatedRevenue += $estimatedRevenue;
            $totalActualRevenue += $actualRevenue;
        }
        
        // 3. BUDGET EFFICIENCY (Estimated vs Actual Hours)
        $totalEstimatedHours = DB::table('projects')
            ->where(function($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', now());
            })
            ->sum('estimated_hours') ?? 0;
        
        $totalActualHours = DB::table('time_entries')
            ->whereIn('project_id', function($query) {
                $query->select('id')
                      ->from('projects')
                      ->where(function($q) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', now());
                      });
            })
            ->sum('hours') ?? 0;
        
        $budgetEfficiency = $totalEstimatedHours > 0 
            ? round(($totalEstimatedHours / max($totalActualHours, 1)) * 100, 1)
            : 100;
        
        // 4. TEAM UTILIZATION - Basierend auf ECHTEN gebuchten Stunden aus MOCO
        $employees = DB::table('employees')
            ->where('is_active', true)
            ->get();
        
        // Zeiträume definieren: Letzte 30 Tage für bessere Datenabdeckung
        $periodStart = now()->subDays(30)->startOfDay();
        $periodEnd = now()->endOfDay();
        $previousPeriodStart = now()->subDays(60)->startOfDay();
        $previousPeriodEnd = now()->subDays(31)->endOfDay();
        
        $employeeWorkloads = [];
        $totalHoursThisWeek = 0;
        $overloadedEmployees = 0;
        
        foreach ($employees as $employee) {
            // Echte gebuchte Stunden aus time_entries (MOCO) - Letzte 30 Tage
            $hoursThisWeek = DB::table('time_entries')
                ->where('employee_id', $employee->id)
                ->whereBetween('date', [$periodStart, $periodEnd])
                ->sum('hours');
            
            $hoursLastWeek = DB::table('time_entries')
                ->where('employee_id', $employee->id)
                ->whereBetween('date', [$previousPeriodStart, $previousPeriodEnd])
                ->sum('hours');
            
            // Top 3 Projekte (letzte 30 Tage)
            $topProjects = DB::table('time_entries')
                ->join('projects', 'time_entries.project_id', '=', 'projects.id')
                ->where('time_entries.employee_id', $employee->id)
                ->whereBetween('time_entries.date', [$periodStart, $periodEnd])
                ->select('projects.name', DB::raw('SUM(time_entries.hours) as hours'))
                ->groupBy('projects.id', 'projects.name')
                ->orderByDesc('hours')
                ->limit(3)
                ->get();
            
            // Anzahl aktiver Projekte
            $activeProjectCount = DB::table('assignments')
                ->where('employee_id', $employee->id)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->count();
            
            $totalHoursThisWeek += $hoursThisWeek;
            
            // Trend berechnen (Vergleich zum Vormonat)
            $trend = $hoursLastWeek > 0 
                ? round((($hoursThisWeek - $hoursLastWeek) / $hoursLastWeek) * 100, 0)
                : ($hoursThisWeek > 0 ? 100 : 0);
            
            // Schwellwerte für 30 Tage: 180h = viel, 120h = normal, <80h = wenig
            if ($hoursThisWeek > 180) {
                $overloadedEmployees++;
            }
            
            $employeeWorkloads[] = [
                'id' => $employee->id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'hours_this_week' => round($hoursThisWeek, 1),
                'hours_last_week' => round($hoursLastWeek, 1),
                'trend' => $trend,
                'project_count' => $activeProjectCount,
                'top_projects' => $topProjects,
                'status' => $hoursThisWeek > 180 ? 'high' : ($hoursThisWeek >= 120 ? 'normal' : 'low')
            ];
        }
        
        // Sortieren nach Stunden diese Woche (absteigend)
        usort($employeeWorkloads, function($a, $b) {
            return $b['hours_this_week'] <=> $a['hours_this_week'];
        });
        
        $averageUtilization = count($employeeWorkloads) > 0 
            ? round($totalHoursThisWeek / count($employeeWorkloads), 1)
            : 0;
        
        // 5. PROJECT DISTRIBUTION & STATUS
        $projectDistribution = [
            'active' => $activeProjects,
            'completed' => $completedProjects,
            'planning' => $planningProjects,
            'total' => $totalProjects
        ];
        
        // Calculate percentages for pie chart
        $projectDistributionPercentages = [
            'active' => $totalProjects > 0 ? round(($activeProjects / $totalProjects) * 100, 1) : 0,
            'completed' => $totalProjects > 0 ? round(($completedProjects / $totalProjects) * 100, 1) : 0,
            'planning' => $totalProjects > 0 ? round(($planningProjects / $totalProjects) * 100, 1) : 0
        ];
        
        // 6. CRITICAL PROJECTS (Overdue & At Risk)
        $criticalProjects = [];
        $overdueProjects = DB::table('projects')
            ->whereNotNull('end_date')
            ->where('end_date', '<', now())
            ->where(function($query) {
                $query->where('progress', '<', 100)
                      ->orWhereNull('progress');
            })
            ->select('id', 'name', 'end_date', 'progress')
            ->orderBy('end_date', 'asc')
            ->limit(5)
            ->get();
            
        foreach ($overdueProjects as $project) {
            $criticalProjects[] = [
                'id' => $project->id,
                'name' => $project->name,
                'type' => 'overdue',
                'end_date' => $project->end_date,
                'progress' => $project->progress ?? 0,
                'days_overdue' => Carbon::parse($project->end_date)->diffInDays(now())
            ];
        }
        
        // 7. PROJECT HEALTH & PERFORMANCE
        $projectHealth = [
            'on_track' => 0,
            'at_risk' => 0,
            'delayed' => 0
        ];
        
        $detailedProjects = [];
        
        foreach ($activeProjectsData as $project) {
            $progress = $project->progress ?? 0;
            $daysTotal = $project->start_date && $project->end_date 
                ? Carbon::parse($project->start_date)->diffInDays(Carbon::parse($project->end_date))
                : 1;
            $daysPassed = $project->start_date 
                ? Carbon::parse($project->start_date)->diffInDays(now())
                : 0;
            $expectedProgress = $daysTotal > 0 ? ($daysPassed / $daysTotal) * 100 : 0;
            
            $status = 'on_track';
            if ($progress < $expectedProgress - 15) {
                $status = 'delayed';
                $projectHealth['delayed']++;
                
                // Add to critical projects if not already overdue
                if (!$overdueProjects->contains('id', $project->id)) {
                    $criticalProjects[] = [
                        'id' => $project->id,
                        'name' => $project->name,
                        'type' => 'delayed',
                        'end_date' => $project->end_date,
                        'progress' => round($progress, 0),
                        'expected_progress' => round($expectedProgress, 0)
                    ];
                }
            } elseif ($progress < $expectedProgress - 5) {
                $status = 'at_risk';
                $projectHealth['at_risk']++;
                
                $criticalProjects[] = [
                    'id' => $project->id,
                    'name' => $project->name,
                    'type' => 'at_risk',
                    'end_date' => $project->end_date,
                    'progress' => round($progress, 0),
                    'expected_progress' => round($expectedProgress, 0)
                ];
            } else {
                $projectHealth['on_track']++;
            }
            
            $detailedProjects[] = [
                'id' => $project->id,
                'name' => $project->name,
                'progress' => round($progress, 0),
                'expected_progress' => round($expectedProgress, 0),
                'status' => $status,
                'end_date' => $project->end_date,
                'estimated_revenue' => ($project->estimated_hours ?? 0) * ($project->hourly_rate ?? 0)
            ];
        }
        
        // Sort critical projects by urgency
        usort($criticalProjects, function($a, $b) {
            $typeOrder = ['overdue' => 1, 'delayed' => 2, 'at_risk' => 3];
            if ($a['type'] !== $b['type']) {
                return $typeOrder[$a['type']] <=> $typeOrder[$b['type']];
            }
            return Carbon::parse($a['end_date'])->timestamp <=> Carbon::parse($b['end_date'])->timestamp;
        });
        
        $criticalProjects = array_slice($criticalProjects, 0, 8); // Limit to 8 most critical
        
        usort($detailedProjects, function($a, $b) {
            return $b['estimated_revenue'] <=> $a['estimated_revenue'];
        });
        
        $projectPerformanceScore = $activeProjects > 0 
            ? round(($projectHealth['on_track'] / $activeProjects) * 100, 0)
            : 100;
        
        // 6. TIME TRACKING INSIGHTS (Last 7 days)
        $last7Days = Carbon::now()->subDays(7);
        $recentTimeEntries = DB::table('time_entries')
            ->where('date', '>=', $last7Days)
            ->get();
        
        $totalHoursLast7Days = $recentTimeEntries->sum('hours');
        $averageHoursPerDay = $totalHoursLast7Days / 7;
        
        // 7. REVENUE TREND (Last 6 months)
        $revenueTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $monthStart = $monthDate->copy()->startOfMonth();
            $monthEnd = $monthDate->copy()->endOfMonth();
            
            $monthlyHours = DB::table('time_entries')
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->sum('hours') ?? 0;
            
            // Estimate revenue based on average hourly rate
            $avgRate = DB::table('projects')->avg('hourly_rate') ?? 0;
            $monthlyRevenue = $monthlyHours * $avgRate;
            
            $revenueTrend[] = [
                'month' => $monthDate->format('M Y'),
                'revenue' => round($monthlyRevenue, 2),
                'hours' => round($monthlyHours, 1)
            ];
        }
        
        // 8. PROJECT PIPELINE (Upcoming projects by month)
        $projectPipeline = [];
        $pipelineProjects = DB::table('projects')
            ->where('start_date', '>', now())
            ->orderBy('start_date', 'asc')
            ->select('id', 'name', 'start_date', 'end_date', 'estimated_hours', 'hourly_rate')
            ->get();
        
        // Group by month
        $monthlyPipeline = [];
        foreach ($pipelineProjects as $project) {
            $monthKey = Carbon::parse($project->start_date)->format('Y-m');
            $monthLabel = Carbon::parse($project->start_date)->format('M Y');
            
            if (!isset($monthlyPipeline[$monthKey])) {
                $monthlyPipeline[$monthKey] = [
                    'month' => $monthLabel,
                    'month_key' => $monthKey,
                    'count' => 0,
                    'total_revenue' => 0,
                    'projects' => []
                ];
            }
            
            $estimatedRevenue = ($project->estimated_hours ?? 0) * ($project->hourly_rate ?? 0);
            
            $monthlyPipeline[$monthKey]['count']++;
            $monthlyPipeline[$monthKey]['total_revenue'] += $estimatedRevenue;
            $monthlyPipeline[$monthKey]['projects'][] = [
                'id' => $project->id,
                'name' => $project->name,
                'start_date' => $project->start_date,
                'estimated_revenue' => $estimatedRevenue
            ];
        }
        
        // Get next 6 months
        $projectPipeline = array_slice($monthlyPipeline, 0, 6);
        
        // 9. ABSENCES & AVAILABILITY
        $currentAbsences = DB::table('absences')
            ->join('employees', 'absences.employee_id', '=', 'employees.id')
            ->where('absences.start_date', '<=', now())
            ->where('absences.end_date', '>=', now())
            ->select('absences.*', 'employees.first_name', 'employees.last_name')
            ->get();
        
        $upcomingAbsences = DB::table('absences')
            ->join('employees', 'absences.employee_id', '=', 'employees.id')
            ->where('absences.start_date', '>', now())
            ->where('absences.start_date', '<=', now()->addDays(30))
            ->select('absences.*', 'employees.first_name', 'employees.last_name')
            ->orderBy('absences.start_date')
            ->limit(5)
            ->get();
        
        $activeEmployeesCount = $employees->count() - $currentAbsences->count();
        
        // 9. TOP PROJECTS (by revenue)
        $topProjects = collect($detailedProjects)->take(5);
        
        // 10. ALERTS & WARNINGS
        $alerts = [
            'overloaded_employees' => $overloadedEmployees,
            'delayed_projects' => $projectHealth['delayed'],
            'low_utilization' => collect($employeeWorkloads)->filter(fn($e) => $e['hours_this_week'] < 80)->count()
        ];
        
        // ==================== LEGACY DATA (for compatibility) ====================
        $employeesCount = DB::table('employees')->count();
        $projectsCount = $totalProjects;
        $teamsCount = DB::table('teams')->count();
        $assignmentsCount = DB::table('assignments')->count();
        $activeAssignmentsCount = DB::table('assignments')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->count();
        
        $recentProjects = DB::table('projects')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // ==================== RETURN TO VIEW ====================
        return view('dashboard', compact(
            // Executive KPIs
            'totalProjects',
            'projectDistribution',
            'projectDistributionPercentages',
            'criticalProjects',
            'projectPipeline',
            'activeProjects',
            'completedProjects',
            'planningProjects',
            'totalEstimatedRevenue',
            'totalActualRevenue',
            'budgetEfficiency',
            'averageUtilization',
            'projectPerformanceScore',
            'projectHealth',
            'employeeWorkloads',
            'overloadedEmployees',
            'detailedProjects',
            'topProjects',
            'revenueTrend',
            'currentAbsences',
            'upcomingAbsences',
            'alerts',
            'totalHoursLast7Days',
            'averageHoursPerDay',
            // Legacy
            'employeesCount',
            'activeEmployeesCount',
            'projectsCount',
            'teamsCount',
            'assignmentsCount',
            'activeAssignmentsCount',
            'recentProjects'
        ));
    }
}
