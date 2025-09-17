<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Export employees to CSV
     */
    public function export()
    {
        $employees = DB::table('employees')->where('is_active', true)->get();

        $filename = 'mitarbeiter-auslastung-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($employees) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM für Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header
            fputcsv($file, ['Mitarbeiter', 'Abteilung', 'Wochenkapazität (h)', 'Verplant (h)', 'Verfügbar (h)', 'Auslastung (%)'], ';');

            // Daten
            foreach ($employees as $employee) {
                $assignments = DB::table('assignments')
                    ->where('employee_id', $employee->id)
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->sum('weekly_hours');

                $utilization = $employee->weekly_capacity > 0
                    ? round(($assignments / $employee->weekly_capacity) * 100)
                    : 0;

                fputcsv($file, [
                    $employee->first_name . ' ' . $employee->last_name,
                    $employee->department,
                    $employee->weekly_capacity,
                    $assignments,
                    $employee->weekly_capacity - $assignments,
                    $utilization
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display a listing of the employees.
     */
    public function index()
    {
        $employees = DB::table('employees')->get();

        // Load assignments for each employee
        foreach ($employees as $employee) {
            $employee->assignments = DB::table('assignments')
                ->where('employee_id', $employee->id)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->get();
        }

        return view('employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        return view('employees.create');
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'weekly_capacity' => 'required|numeric|min:0|max:40',
        ]);

        DB::table('employees')->insert([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'department' => $validated['department'],
            'weekly_capacity' => $validated['weekly_capacity'],
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('employees.index')->with('success', 'Mitarbeiter erfolgreich angelegt!');
    }

    /**
     * Display the specified employee.
     */
    public function show($id)
    {
        $employee = DB::table('employees')->find($id);

        if (!$employee) {
            abort(404);
        }

        $assignments = DB::table('assignments')
            ->join('projects', 'assignments.project_id', '=', 'projects.id')
            ->where('assignments.employee_id', $id)
            ->select('assignments.*', 'projects.name as project_name')
            ->get();

        return view('employees.show', compact('employee', 'assignments'));
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit($id)
    {
        $employee = DB::table('employees')->find($id);

        if (!$employee) {
            abort(404);
        }

        return view('employees.edit', compact('employee'));
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'weekly_capacity' => 'required|numeric|min:0|max:40',
        ]);

        DB::table('employees')
            ->where('id', $id)
            ->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'department' => $validated['department'],
                'weekly_capacity' => $validated['weekly_capacity'],
                'updated_at' => now(),
            ]);

        return redirect()->route('employees.index')->with('success', 'Mitarbeiter erfolgreich aktualisiert!');
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy($id)
    {
        DB::table('employees')->where('id', $id)->delete();

        return redirect()->route('employees.index')->with('success', 'Mitarbeiter erfolgreich gelöscht!');
    }
}
