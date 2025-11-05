<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;

class AbsenceController extends Controller
{
    public function index(Request $request)
    {
        // Basis-Query
        $query = Absence::with('employee');

        // Filter: Mitarbeiter
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter: Typ (urlaub, krankheit, fortbildung)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter: Datums-Range
        if ($request->filled('from')) {
            $query->where('start_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('end_date', '<=', $request->to);
        }

        // Gruppierung nach Mitarbeiter für Accordion-View
        $absencesByEmployee = $query->orderBy('start_date', 'desc')
            ->get()
            ->groupBy('employee_id')
            ->map(function ($employeeAbsences) {
                return [
                    'employee' => $employeeAbsences->first()->employee,
                    'absences' => $employeeAbsences->sortByDesc('start_date')->values(),
                    'total_count' => $employeeAbsences->count(),
                    'total_days' => $employeeAbsences->sum(function ($absence) {
                        return \Carbon\Carbon::parse($absence->start_date)
                            ->diffInDays(\Carbon\Carbon::parse($absence->end_date)) + 1;
                    }),
                    'by_type' => [
                        'urlaub' => $employeeAbsences->where('type', 'urlaub')->count(),
                        'krankheit' => $employeeAbsences->where('type', 'krankheit')->count(),
                        'fortbildung' => $employeeAbsences->where('type', 'fortbildung')->count(),
                    ]
                ];
            });

        // Globale Statistiken
        $stats = [
            'total' => Absence::count(),
            'urlaub' => Absence::where('type', 'urlaub')->count(),
            'krankheit' => Absence::where('type', 'krankheit')->count(),
            'fortbildung' => Absence::where('type', 'fortbildung')->count(),
        ];

        // Mitarbeiter-Liste für Filter-Dropdown
        $employees = Employee::orderBy('last_name')->orderBy('first_name')->get();

        return view('absences.index', compact('absencesByEmployee', 'stats', 'employees'));
    }

    public function importForm()
    {
        $employees = Employee::where('is_active', true)->get();
        return view('absences.import', compact('employees'));
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
                if (count($data) >= 4) {
                    // Find employee by name
                    $employeeName = $data[0];
                    $employee = Employee::whereRaw("CONCAT(first_name, ' ', last_name) = ?", [$employeeName])->first();
                    
                    if ($employee) {
                        Absence::create([
                            'employee_id' => $employee->id,
                            'type' => $data[1] === 'Urlaub' ? 'urlaub' : ($data[1] === 'Krank' ? 'krankheit' : 'fortbildung'),
                            'start_date' => Carbon::createFromFormat('d.m.Y', $data[2]),
                            'end_date' => Carbon::createFromFormat('d.m.Y', $data[3]),
                            'reason' => $data[4] ?? null,
                        ]);
                        $imported++;
                    } else {
                        $errors[] = "Zeile " . ($imported + 1) . ": Mitarbeiter nicht gefunden";
                    }
                }
            } catch (Exception $e) {
                $errors[] = "Zeile " . ($imported + 1) . ": " . $e->getMessage();
            }
        }
        
        fclose($handle);
        
        $message = "Erfolgreich {$imported} Abwesenheiten importiert.";
        if (!empty($errors)) {
            $message .= " Fehler: " . implode(', ', $errors);
        }
        
        return redirect()->route('absences.index')->with('success', $message);
    }

    public function export()
    {
        $absences = Absence::with('employee')->get();

        $filename = 'abwesenheiten-uebersicht-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($absences) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM für Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header
            fputcsv($file, ['Mitarbeiter', 'Typ', 'Startdatum', 'Enddatum', 'Dauer (Tage)', 'Grund', 'Status'], ';');

            // Daten
            foreach ($absences as $absence) {
                $isActive = $absence->start_date <= now() && $absence->end_date >= now();
                $isUpcoming = $absence->start_date > now();
                $isCompleted = $absence->end_date < now();
                
                $status = $isActive ? 'Aktiv' : ($isUpcoming ? 'Geplant' : 'Beendet');
                $duration = Carbon::parse($absence->start_date)->diffInDays(Carbon::parse($absence->end_date)) + 1;

                fputcsv($file, [
                    $absence->employee->first_name . ' ' . $absence->employee->last_name,
                    $absence->type == 'urlaub' ? 'Urlaub' : ($absence->type == 'krankheit' ? 'Krank' : 'Fortbildung'),
                    Carbon::parse($absence->start_date)->format('d.m.Y'),
                    Carbon::parse($absence->end_date)->format('d.m.Y'),
                    $duration,
                    $absence->reason ?? '',
                    $status
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function create()
    {
        $employees = Employee::where('is_active', true)->get();
        return view('absences.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:urlaub,krankheit,fortbildung',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string'
        ]);

        Absence::create($validated);
        return redirect('/absences')->with('success', 'Abwesenheit erfolgreich eingetragen');
    }

    public function edit(Absence $absence)
    {
        $employees = Employee::where('is_active', true)->get();
        return view('absences.edit', compact('absence', 'employees'));
    }

    public function update(Request $request, Absence $absence)
    {
        $validated = $request->validate([
            'type' => 'required|in:urlaub,krankheit,fortbildung',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string'
        ]);

        $absence->update($validated);
        return redirect('/absences')->with('success', 'Abwesenheit erfolgreich aktualisiert');
    }

    public function destroy(Absence $absence)
    {
        $absence->delete();
        return redirect('/absences')->with('success', 'Abwesenheit erfolgreich gelöscht');
    }

    public function show(Absence $absence)
    {
        $absence->load('employee');

        $start = Carbon::parse($absence->start_date);
        $end = Carbon::parse($absence->end_date);
        $now = now();

        $isActive = $start <= $now && $end >= $now;
        $isUpcoming = $start > $now;
        $isCompleted = $end < $now;

        $durationDays = $start->diffInDays($end) + 1;
        $remainingDays = (int) ($end->isFuture() ? $now->diffInDays($end) + 1 : 0);

        return view('absences.show', compact(
            'absence',
            'start',
            'end',
            'isActive',
            'isUpcoming',
            'isCompleted',
            'durationDays',
            'remainingDays'
        ));
    }
}
