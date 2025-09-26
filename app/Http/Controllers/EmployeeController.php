<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use Exception;

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
     * Show import form
     */
    public function importForm()
    {
        return view('employees.import');
    }

    /**
     * Process import
     */
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
                    Employee::create([
                        'first_name' => $data[0],
                        'last_name' => $data[1],
                        'department' => $data[2],
                        'weekly_capacity' => (int)$data[3],
                        'is_active' => $data[4] === '1' || strtolower($data[4]) === 'true',
                        'email' => $data[5] ?? null,
                    ]);
                    $imported++;
                }
            } catch (Exception $e) {
                $errors[] = "Zeile " . ($imported + 1) . ": " . $e->getMessage();
            }
        }
        
        fclose($handle);
        
        $message = "Erfolgreich {$imported} Mitarbeiter importiert.";
        if (!empty($errors)) {
            $message .= " Fehler: " . implode(', ', $errors);
        }
        
        return redirect()->route('employees.index')->with('success', $message);
    }

    /**
     * Display a listing of the employees.
     */
    public function index()
    {
        $employees = Employee::with(['assignments' => function($query) {
            $query->where('start_date', '<=', now())
                  ->where('end_date', '>=', now());
        }, 'assignments.project'])
        ->orderBy('is_active', 'desc')
        ->orderBy('last_name', 'asc')
        ->get();

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
    public function show(Employee $employee)
    {

        $assignments = DB::table('assignments')
            ->join('projects', 'assignments.project_id', '=', 'projects.id')
            ->where('assignments.employee_id', $employee->id)
            ->select('assignments.*', 'projects.name as project_name')
            ->get();

        return view('employees.show', compact('employee', 'assignments'));
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'weekly_capacity' => 'required|numeric|min:0|max:40',
        ]);

        DB::table('employees')
            ->where('id', $employee->id)
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
    public function destroy(Employee $employee)
    {
        DB::table('employees')->where('id', $employee->id)->delete();

        return redirect()->route('employees.index')->with('success', 'Mitarbeiter erfolgreich gelöscht!');
    }
}
