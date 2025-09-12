<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('assignments')->get();
        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|max:50',
            'last_name' => 'required|max:50',
            'department' => 'required|max:100',
            'weekly_capacity' => 'required|integer|min:1|max:60',
        ]);

        Employee::create($validated);
        return redirect('/employees')->with('success', 'Mitarbeiter erfolgreich angelegt');
    }

    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'first_name' => 'required|max:50',
            'last_name' => 'required|max:50',
            'department' => 'required|max:100',
            'weekly_capacity' => 'required|integer|min:1|max:60',
        ]);

        $employee->update($validated);
        return redirect('/employees')->with('success', 'Mitarbeiter erfolgreich aktualisiert');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect('/employees')->with('success', 'Mitarbeiter erfolgreich gelöscht');
    }
}
