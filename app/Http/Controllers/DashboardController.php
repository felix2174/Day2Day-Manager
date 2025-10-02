<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Mitarbeiter mit ihren Zuweisungen laden
        $employees = DB::table('employees')
            ->where('is_active', true)
            ->get();
        
        $employeeWorkloads = [];
        $totalCapacity = 0;
        $totalAssigned = 0;
        
        foreach ($employees as $employee) {
            // Aktuelle Zuweisungen für diesen Mitarbeiter
            $assignments = DB::table('assignments')
                ->join('projects', 'assignments.project_id', '=', 'projects.id')
                ->where('assignments.employee_id', $employee->id)
                ->where('assignments.start_date', '<=', now())
                ->where('assignments.end_date', '>=', now())
                ->select('assignments.*', 'projects.name as project_name')
                ->get();
            
            $assignedHours = $assignments->sum('weekly_hours');
            $weeklyCapacity = $employee->weekly_capacity ?? 40;
            $freeHours = max(0, $weeklyCapacity - $assignedHours);
            $utilization = $weeklyCapacity > 0 ? ($assignedHours / $weeklyCapacity) * 100 : 0;
            
            $totalCapacity += $weeklyCapacity;
            $totalAssigned += $assignedHours;
            
            $employeeWorkloads[] = [
                'employee' => $employee,
                'assignments' => $assignments,
                'assigned_hours' => $assignedHours,
                'weekly_capacity' => $weeklyCapacity,
                'free_hours' => $freeHours,
                'utilization' => round($utilization, 0)
            ];
        }
        
        // Sortiere nach Auslastung (höchste zuerst)
        usort($employeeWorkloads, function($a, $b) {
            return $b['utilization'] <=> $a['utilization'];
        });
        
        // Projekte laden mit automatischer Fortschritts-Berechnung
        $projects = \App\Models\Project::whereIn('status', ['active', 'planning'])
            ->with(['assignments', 'timeEntries'])
            ->get();
        
        $projectData = [];
        foreach ($projects as $project) {
            // Automatischen Fortschritt berechnen
            $automaticProgress = $project->calculateAutomaticProgress();
            
            // Fortschritt aktualisieren falls nötig
            if (abs($project->progress - $automaticProgress) > 1) {
                $project->updateProgress();
            }
            
            $projectAssignments = $project->assignments->sum('weekly_hours');
            $totalHoursWorked = $project->timeEntries->sum('hours');
            
            $projectData[] = [
                'project' => $project,
                'weekly_hours' => $projectAssignments,
                'progress' => $automaticProgress,
                'total_hours_worked' => $totalHoursWorked,
                'progress_details' => $project->getProgressDetails()
            ];
        }
        
        // Abwesenheiten für die nächsten 30 Tage
        $absences = DB::table('absences')
            ->join('employees', 'absences.employee_id', '=', 'employees.id')
            ->where('absences.start_date', '>=', now())
            ->where('absences.start_date', '<=', now()->addDays(30))
            ->select(
                'absences.*',
                'employees.first_name',
                'employees.last_name'
            )
            ->orderBy('absences.start_date')
            ->get();
        
        // Ressourcen-Übersicht
        $resourceOverview = [
            'total_capacity' => $totalCapacity,
            'total_assigned' => $totalAssigned,
            'total_available' => max(0, $totalCapacity - $totalAssigned)
        ];
        
        // Aktuelle Kalenderwoche
        $currentWeek = Carbon::now()->weekOfYear;
        
        // Zusätzliche Statistiken für das neue Dashboard - konsistente Definitionen
        $employeesCount = DB::table('employees')->count();
        $activeEmployeesCount = DB::table('employees')->where('is_active', true)->count();
        $projectsCount = DB::table('projects')->count();
        $activeProjectsCount = DB::table('projects')->whereIn('status', ['active', 'planning'])->count();
        $teamsCount = DB::table('teams')->count();
        $assignmentsCount = DB::table('assignments')->count();
        $activeAssignmentsCount = DB::table('assignments')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->count();

        // Recent Projects
        $recentProjects = DB::table('projects')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Current Absences
        $currentAbsences = DB::table('absences')
            ->join('employees', 'absences.employee_id', '=', 'employees.id')
            ->where('absences.start_date', '<=', now())
            ->where('absences.end_date', '>=', now())
            ->select('absences.*', 'employees.first_name', 'employees.last_name')
            ->get();

        return view('dashboard', compact(
            'employeeWorkloads',
            'projectData',
            'absences',
            'resourceOverview',
            'currentWeek',
            'employeesCount',
            'activeEmployeesCount',
            'projectsCount',
            'activeProjectsCount',
            'teamsCount',
            'assignmentsCount',
            'activeAssignmentsCount',
            'recentProjects',
            'currentAbsences'
        ));
    }
}
