<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Absence;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AssignmentController extends Controller
{
    public function index()
    {
        $assignments = Assignment::with(['employee', 'project'])->get();
        return view('assignments.index', compact('assignments'));
    }

    public function create(Request $request)
    {
        $projects = Project::where('status', 'active')->get();
        $selectedProject = null;
        $availableEmployees = collect();

        if ($request->has('project_id') && $request->has('weekly_hours')) {
            $selectedProject = Project::find($request->project_id);
            $requiredHours = $request->weekly_hours;

            // Nur Mitarbeiter mit genug freien Stunden
            $availableEmployees = Employee::where('is_active', true)
                ->get()
                ->filter(function ($employee) use ($requiredHours) {
                    $usedHours = $employee->assignments->sum('weekly_hours');
                    $freeHours = $employee->weekly_capacity - $usedHours;
                    return $freeHours >= $requiredHours;
                })
                ->map(function ($employee) use ($requiredHours) {
                    $usedHours = $employee->assignments->sum('weekly_hours');
                    $employee->free_hours = $employee->weekly_capacity - $usedHours;
                    $employee->can_assign = $employee->free_hours >= $requiredHours;
                    return $employee;
                });
        }

        return view('assignments.create', compact('projects', 'selectedProject', 'availableEmployees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'employee_id' => 'required|exists:employees,id',
            'weekly_hours' => 'required|integer|min:1|max:60',
            'priority_level' => 'required|in:low,medium,high',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date'
        ]);

        // Kapazitätsprüfung
        $employee = Employee::find($validated['employee_id']);
        $currentHours = $employee->assignments->sum('weekly_hours');
        $newTotal = $currentHours + $validated['weekly_hours'];

        if ($newTotal > $employee->weekly_capacity) {
            return back()->with('error', 'Mitarbeiter wäre mit ' . $newTotal . 'h überlastet (Kapazität: ' . $employee->weekly_capacity . 'h)');
        }

        Assignment::create($validated);

        // Hier könnte eine E-Mail-Benachrichtigung erfolgen

        return redirect('/dashboard')->with('success', 'Zuweisung erfolgreich erstellt');
    }

    public function edit(Assignment $assignment)
    {
        $projects = Project::where('status', 'active')->get();
        $employees = Employee::where('is_active', true)->get();
        return view('assignments.edit', compact('assignment', 'projects', 'employees'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'weekly_hours' => 'required|integer|min:1|max:60',
            'priority_level' => 'required|in:low,medium,high',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date'
        ]);

        // Kapazitätsprüfung
        $employee = $assignment->employee;
        $currentHours = $employee->assignments->where('id', '!=', $assignment->id)->sum('weekly_hours');
        $newTotal = $currentHours + $validated['weekly_hours'];

        if ($newTotal > $employee->weekly_capacity) {
            return back()->with('error', 'Überlastung: ' . $newTotal . 'h von ' . $employee->weekly_capacity . 'h');
        }

        $assignment->update($validated);
        return redirect('/assignments')->with('success', 'Zuweisung aktualisiert');
    }

    public function destroy(Assignment $assignment)
    {
        $assignment->delete();
        return redirect('/assignments')->with('success', 'Zuweisung gelöscht');
    }
}
