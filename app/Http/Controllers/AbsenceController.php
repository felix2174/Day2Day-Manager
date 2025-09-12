<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Employee;
use Illuminate\Http\Request;

class AbsenceController extends Controller
{
    public function index()
    {
        $absences = Absence::with('employee')->orderBy('start_date', 'desc')->get();
        return view('absences.index', compact('absences'));
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
}
