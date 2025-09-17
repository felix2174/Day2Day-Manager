<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Display the specified project.
     */
    public function show($id)
    {
        $project = DB::table('projects')->find($id);

        if (!$project) {
            abort(404);
        }

        // Lade alle Zuweisungen mit Mitarbeiter-Details
        $assignments = DB::table('assignments')
            ->join('employees', 'assignments.employee_id', '=', 'employees.id')
            ->where('assignments.project_id', $id)
            ->select(
                'assignments.*',
                DB::raw("CONCAT(employees.first_name, ' ', employees.last_name) as employee_name"),
                'employees.department as employee_department'
            )
            ->get();

        return view('projects.show', compact('project', 'assignments'));
    }

    public function index()
    {
        $projects = Project::withCount('assignments')->get();
        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:100',
            'description' => 'nullable',
            'status' => 'required|in:planning,active,completed',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date'
        ]);

        Project::create($validated);
        return redirect('/projects')->with('success', 'Projekt erfolgreich angelegt');
    }

    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|max:100',
            'description' => 'nullable',
            'status' => 'required|in:planning,active,completed',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date'
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
