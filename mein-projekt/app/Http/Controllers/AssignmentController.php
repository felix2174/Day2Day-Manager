<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Absence;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;

class AssignmentController extends Controller
{
    public function index()
    {
        $assignments = Assignment::with(['employee', 'project'])
            ->orderBy('start_date', 'desc')
            ->get();

        return view('assignments.index', compact('assignments'));
    }

    public function importForm()
    {
        $employees = Employee::where('is_active', true)->get();
        $projects = Project::where('status', 'active')->get();
        return view('assignments.import', compact('employees', 'projects'));
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
                    // Find employee by name
                    $employeeName = $data[0];
                    $employee = Employee::whereRaw("CONCAT(first_name, ' ', last_name) = ?", [$employeeName])->first();
                    
                    // Find project by name
                    $projectName = $data[1];
                    $project = Project::where('name', $projectName)->first();
                    
                    if ($employee && $project) {
                        Assignment::create([
                            'employee_id' => $employee->id,
                            'project_id' => $project->id,
                            'weekly_hours' => (int)$data[2],
                            'start_date' => Carbon::createFromFormat('d.m.Y', $data[3]),
                            'end_date' => Carbon::createFromFormat('d.m.Y', $data[4]),
                            'priority' => $data[5] ?? 'medium',
                        ]);
                        $imported++;
                    } else {
                        $errors[] = "Zeile " . ($imported + 1) . ": Mitarbeiter oder Projekt nicht gefunden";
                    }
                }
            } catch (Exception $e) {
                $errors[] = "Zeile " . ($imported + 1) . ": " . $e->getMessage();
            }
        }
        
        fclose($handle);
        
        $message = "Erfolgreich {$imported} Zuweisungen importiert.";
        if (!empty($errors)) {
            $message .= " Fehler: " . implode(', ', $errors);
        }
        
        return redirect()->route('assignments.index')->with('success', $message);
    }

    public function export()
    {
        $assignments = Assignment::with(['employee', 'project'])->get();

        $filename = 'zuweisungen-uebersicht-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($assignments) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM für Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header
            fputcsv($file, ['Mitarbeiter', 'Projekt', 'Wochenstunden', 'Startdatum', 'Enddatum', 'Priorität', 'Status'], ';');

            // Daten
            foreach ($assignments as $assignment) {
                $isActive = $assignment->start_date <= now() && $assignment->end_date >= now();
                $isUpcoming = $assignment->start_date > now();
                $isCompleted = $assignment->end_date < now();
                
                $status = $isActive ? 'Aktiv' : ($isUpcoming ? 'Geplant' : 'Abgeschlossen');

                fputcsv($file, [
                    $assignment->employee->first_name . ' ' . $assignment->employee->last_name,
                    $assignment->project->name,
                    $assignment->weekly_hours,
                    Carbon::parse($assignment->start_date)->format('d.m.Y'),
                    Carbon::parse($assignment->end_date)->format('d.m.Y'),
                    ucfirst($assignment->priority),
                    $status
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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

    public function show(Assignment $assignment)
    {
        $assignment->load(['employee.assignments', 'project']);

        $start = Carbon::parse($assignment->start_date);
        $end = Carbon::parse($assignment->end_date);
        $now = now();

        $isActive = $start <= $now && $end >= $now;
        $isUpcoming = $start > $now;
        $isCompleted = $end < $now;
        $remainingDays = (int) ($end->isFuture() ? $now->diffInDays($end) : 0);

        // Kapazität des Mitarbeiters (inkl. dieser Zuweisung)
        $usedHours = $assignment->employee->assignments->sum('weekly_hours');
        $weeklyCapacity = $assignment->employee->weekly_capacity;
        $freeHours = $weeklyCapacity - $usedHours;

        // Überlappende Zuweisungen beim selben Mitarbeiter
        $overlaps = $assignment->employee->assignments
            ->where('id', '!=', $assignment->id)
            ->filter(function ($a) use ($start, $end) {
                $aStart = Carbon::parse($a->start_date);
                $aEnd = Carbon::parse($a->end_date);
                return $aStart <= $end && $aEnd >= $start; // Zeiträume schneiden sich
            })
            ->values();

        return view('assignments.show', compact(
            'assignment',
            'isActive',
            'isUpcoming',
            'isCompleted',
            'remainingDays',
            'usedHours',
            'weeklyCapacity',
            'freeHours',
            'overlaps',
            'start',
            'end'
        ));
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
