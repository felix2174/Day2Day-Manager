<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class ProjectController extends Controller
{
    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {

        // Lade alle Zuweisungen mit Mitarbeiter-Details
        $assignments = DB::table('assignments')
            ->join('employees', 'assignments.employee_id', '=', 'employees.id')
            ->where('assignments.project_id', $project->id)
            ->select(
                'assignments.*',
                DB::raw("employees.first_name || ' ' || employees.last_name as employee_name"),
                'employees.department as employee_department'
            )
            ->get();

        return view('projects.show', compact('project', 'assignments'));
    }

    public function index()
    {
        $projects = Project::withCount('assignments')
            ->with(['assignments.employee', 'responsible'])
            ->get();
        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $employees = Employee::where('is_active', true)->get();
        return view('projects.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:100',
            'description' => 'nullable',
            'status' => 'required|in:planning,active,completed',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'estimated_hours' => 'nullable|integer|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'progress' => 'nullable|integer|min:0|max:100',
            'responsible_id' => 'nullable|exists:employees,id'
        ]);

        Project::create($validated);
        return redirect('/projects')->with('success', 'Projekt erfolgreich angelegt');
    }

    public function edit(Project $project)
    {
        $employees = Employee::where('is_active', true)->get();
        return view('projects.edit', compact('project', 'employees'));
    }

    public function importForm()
    {
        return view('projects.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');
        
        // Skip header
        fgetcsv($handle, 1000, ';');
        
        $imported = 0;
        $errors = [];
        
        while (($data = fgetcsv($handle, 1000, ';')) !== false) {
            try {
                if (count($data) >= 6) {
                    // Find responsible employee by name
                    $responsibleId = null;
                    if (isset($data[8]) && !empty($data[8])) {
                        $responsibleName = $data[8];
                        $responsible = Employee::whereRaw("CONCAT(first_name, ' ', last_name) = ?", [$responsibleName])->first();
                        if ($responsible) {
                            $responsibleId = $responsible->id;
                        }
                    }

                    Project::create([
                        'name' => $data[0],
                        'description' => $data[1] ?? null,
                        'status' => $data[2] ?? 'planning',
                        'start_date' => $data[3] ? Carbon::createFromFormat('d.m.Y', $data[3]) : null,
                        'end_date' => $data[4] ? Carbon::createFromFormat('d.m.Y', $data[4]) : null,
                        'progress' => (int)($data[5] ?? 0),
                        'estimated_hours' => $data[6] ? (int)$data[6] : null,
                        'hourly_rate' => $data[7] ? (float)$data[7] : null,
                        'responsible_id' => $responsibleId,
                    ]);
                    $imported++;
                }
            } catch (Exception $e) {
                $errors[] = "Zeile " . ($imported + 1) . ": " . $e->getMessage();
            }
        }
        
        fclose($handle);
        
        $message = "Erfolgreich {$imported} Projekte importiert.";
        if (!empty($errors)) {
            $message .= " Fehler: " . implode(', ', $errors);
        }
        
        return redirect()->route('projects.index')->with('success', $message);
    }

    public function export()
    {
        $projects = Project::withCount('assignments')->get();

        $filename = 'projekte-uebersicht-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($projects) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM für Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header
            fputcsv($file, ['Projekt', 'Beschreibung', 'Status', 'Startdatum', 'Enddatum', 'Fortschritt (%)', 'Geschätzte Stunden', 'Stundensatz (€)', 'Verantwortlicher', 'Zuweisungen'], ';');

            // Daten
            foreach ($projects as $project) {
                fputcsv($file, [
                    $project->name,
                    $project->description ?? '',
                    ucfirst($project->status),
                    Carbon::parse($project->start_date)->format('d.m.Y'),
                    $project->end_date ? Carbon::parse($project->end_date)->format('d.m.Y') : '',
                    $project->progress ?? 0,
                    $project->estimated_hours ?? '',
                    $project->hourly_rate ?? '',
                    $project->responsible ? $project->responsible->first_name . ' ' . $project->responsible->last_name : '',
                    $project->assignments_count
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|max:100',
            'description' => 'nullable',
            'status' => 'required|in:planning,active,completed',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'estimated_hours' => 'nullable|integer|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'progress' => 'nullable|integer|min:0|max:100',
            'responsible_id' => 'nullable|exists:employees,id'
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
