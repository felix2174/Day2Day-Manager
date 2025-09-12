<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Project;
use App\Models\Assignment;
use App\Models\Absence;  // <- Diese Zeile fehlt
use Carbon\Carbon;       // <- Diese auch für Carbon

class DashboardController extends Controller
{
    public function index()
    {
        $employees = Employee::with(['assignments.project', 'absences'])->get();
        $projects = Project::all();
        $totalAssignments = Assignment::count();

        // Aktuelle und kommende Abwesenheiten
        $upcomingAbsences = Absence::with('employee')
            ->where('end_date', '>=', Carbon::today())
            ->orderBy('start_date')
            ->get();

        return view('dashboard', compact('employees', 'projects', 'totalAssignments', 'upcomingAbsences'));
    }
}
