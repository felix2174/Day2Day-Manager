<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectAssignmentOverride;
use Illuminate\Http\Request;

class ProjectAssignmentOverrideController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'project_id' => 'nullable|exists:projects,id',
            'project_name' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'weekly_hours' => 'nullable|numeric|min:0',
            'activity' => 'nullable|string|max:255',
            'source_label' => 'nullable|string|max:255',
        ]);

        ProjectAssignmentOverride::create($data);

        return redirect()->route('gantt.index')->with('success', 'Override gespeichert.');
    }
}






