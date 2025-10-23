<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Project;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teams = Team::withCount(['projects'])
            ->with(['projects' => function($query) {
                $query->take(3); // Zeige nur die ersten 3 Projekte
            }])
            ->get();

        return view('teams.index', compact('teams'));
    }

    public function importForm()
    {
        return view('teams.import');
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
                if (count($data) >= 3) {
                    Team::create([
                        'name' => $data[0],
                        'department' => $data[1],
                        'description' => $data[2] ?? null,
                    ]);
                    $imported++;
                }
            } catch (Exception $e) {
                $errors[] = "Zeile " . ($imported + 1) . ": " . $e->getMessage();
            }
        }
        
        fclose($handle);
        
        $message = "Erfolgreich {$imported} Teams importiert.";
        if (!empty($errors)) {
            $message .= " Fehler: " . implode(', ', $errors);
        }
        
        return redirect()->route('teams.index')->with('success', $message);
    }

    public function export()
    {
        $teams = Team::withCount(['projects'])->get();

        $filename = 'teams-uebersicht-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($teams) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM für Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header
            fputcsv($file, ['Team', 'Abteilung', 'Beschreibung', 'Projekte', 'Erstellt am'], ';');

            // Daten
            foreach ($teams as $team) {
                fputcsv($file, [
                    $team->name,
                    $team->department,
                    $team->description ?? '',
                    $team->projects_count,
                    Carbon::parse($team->created_at)->format('d.m.Y')
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('teams.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department' => 'required|string|max:255',
        ]);

        Team::create($request->all());

        return redirect()->route('teams.index')
            ->with('success', 'Team erfolgreich erstellt!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team)
    {
        $team->load(['projects' => function($query) {
            $query->with(['assignments.employee']);
        }]);

        // Hole alle Mitarbeiter, die in Projekten dieses Teams arbeiten
        $employeeIds = DB::table('assignments')
            ->join('projects', 'assignments.project_id', '=', 'projects.id')
            ->join('team_assignments', 'projects.id', '=', 'team_assignments.project_id')
            ->where('team_assignments.team_id', $team->id)
            ->where('assignments.start_date', '<=', now())
            ->where('assignments.end_date', '>=', now())
            ->distinct()
            ->pluck('assignments.employee_id');

        $employees = Employee::whereIn('id', $employeeIds)
            ->where('is_active', true)
            ->get();

        return view('teams.show', compact('team', 'employees'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Team $team)
    {
        return view('teams.edit', compact('team'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Team $team)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department' => 'required|string|max:255',
        ]);

        $team->update($request->all());

        return redirect()->route('teams.index')
            ->with('success', 'Team erfolgreich aktualisiert!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team)
    {
        // Prüfe ob Team noch Projekte zugewiesen hat
        if ($team->projects()->count() > 0) {
            return redirect()->route('teams.index')
                ->with('error', 'Team kann nicht gelöscht werden, da noch Projekte zugewiesen sind!');
        }

        $team->delete();

        return redirect()->route('teams.index')
            ->with('success', 'Team erfolgreich gelöscht!');
    }
}
