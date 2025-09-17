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
        
        // Projekte laden
        $projects = DB::table('projects')
            ->where('status', 'active')
            ->get();
        
        $projectData = [];
        foreach ($projects as $project) {
            $projectAssignments = DB::table('assignments')
                ->where('project_id', $project->id)
                ->sum('weekly_hours');
            
            // Berechne Fortschritt basierend auf Datum
            $startDate = Carbon::parse($project->start_date);
            $endDate = Carbon::parse($project->end_date);
            $today = Carbon::now();
            
            $totalDays = $startDate->diffInDays($endDate);
            $elapsedDays = $startDate->diffInDays($today);
            $progress = $totalDays > 0 ? min(100, ($elapsedDays / $totalDays) * 100) : 0;
            
            $projectData[] = [
                'project' => $project,
                'weekly_hours' => $projectAssignments,
                'progress' => round($progress, 0)
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
        
        return view('dashboard', compact(
            'employeeWorkloads',
            'projectData',
            'absences',
            'resourceOverview',
            'currentWeek'
        ));
    }
}
