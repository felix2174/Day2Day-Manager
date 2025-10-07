<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GanttController extends Controller
{
    public function index()
    {
        $projects = Project::whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->with(['assignments' => function($query) {
                $query->join('employees', 'assignments.employee_id', '=', 'employees.id')
                      ->select('assignments.*', 
                               DB::raw("employees.first_name || ' ' || employees.last_name as employee_name"));
            }])
            ->orderBy('start_date')
            ->get();

        return view('gantt.index', compact('projects'));
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
}
