<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use App\Models\Project;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TimeEntryController extends Controller
{
    /**
     * Zeigt die Zeiterfassung für einen Mitarbeiter
     */
    public function index(Request $request)
    {
        $employeeId = $request->get('employee_id');
        $projectId = $request->get('project_id');
        $dateFrom = $request->get('date_from', now()->startOfWeek()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->endOfWeek()->format('Y-m-d'));

        $query = TimeEntry::with(['employee', 'project']);

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $query->whereBetween('date', [$dateFrom, $dateTo]);

        $timeEntries = $query->orderBy('date', 'desc')->get();

        // Statistiken
        $totalHours = $timeEntries->sum('hours');
        $billableHours = $timeEntries->where('billable', true)->sum('hours');
        $nonBillableHours = $totalHours - $billableHours;

        // Mitarbeiter und Projekte für Filter
        $employees = Employee::where('is_active', true)->orderBy('first_name')->get();
        $projects = Project::whereIn('status', ['active', 'planning'])->orderBy('name')->get();

        return view('time-entries.index', compact(
            'timeEntries', 
            'totalHours', 
            'billableHours', 
            'nonBillableHours',
            'employees',
            'projects',
            'employeeId',
            'projectId',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Zeigt das Formular für neue Zeiterfassung
     */
    public function create(Request $request)
    {
        $employeeId = $request->get('employee_id');
        $projectId = $request->get('project_id');
        $date = $request->get('date', now()->format('Y-m-d'));

        $employees = Employee::where('is_active', true)->orderBy('first_name')->get();
        $projects = Project::whereIn('status', ['active', 'planning'])->orderBy('name')->get();

        return view('time-entries.create', compact('employees', 'projects', 'employeeId', 'projectId', 'date'));
    }

    /**
     * Speichert einen neuen Zeiteintrag
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'project_id' => 'required|exists:projects,id',
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0.1|max:24',
            'description' => 'nullable|string|max:500',
            'billable' => 'boolean'
        ]);

        TimeEntry::create([
            'employee_id' => $request->employee_id,
            'project_id' => $request->project_id,
            'date' => $request->date,
            'hours' => $request->hours,
            'description' => $request->description,
            'billable' => $request->has('billable')
        ]);

        return redirect()->route('time-entries.index')
            ->with('success', 'Zeiteintrag erfolgreich erstellt.');
    }

    /**
     * Zeigt einen Zeiteintrag
     */
    public function show(TimeEntry $timeEntry)
    {
        return view('time-entries.show', compact('timeEntry'));
    }

    /**
     * Zeigt das Bearbeitungsformular
     */
    public function edit(TimeEntry $timeEntry)
    {
        $employees = Employee::where('is_active', true)->orderBy('first_name')->get();
        $projects = Project::whereIn('status', ['active', 'planning'])->orderBy('name')->get();

        return view('time-entries.edit', compact('timeEntry', 'employees', 'projects'));
    }

    /**
     * Aktualisiert einen Zeiteintrag
     */
    public function update(Request $request, TimeEntry $timeEntry)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'project_id' => 'required|exists:projects,id',
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0.1|max:24',
            'description' => 'nullable|string|max:500',
            'billable' => 'boolean'
        ]);

        $timeEntry->update([
            'employee_id' => $request->employee_id,
            'project_id' => $request->project_id,
            'date' => $request->date,
            'hours' => $request->hours,
            'description' => $request->description,
            'billable' => $request->has('billable')
        ]);

        return redirect()->route('time-entries.index')
            ->with('success', 'Zeiteintrag erfolgreich aktualisiert.');
    }

    /**
     * Löscht einen Zeiteintrag
     */
    public function destroy(TimeEntry $timeEntry)
    {
        $timeEntry->delete();

        return redirect()->route('time-entries.index')
            ->with('success', 'Zeiteintrag erfolgreich gelöscht.');
    }

    /**
     * Aktualisiert alle Projektfortschritte
     */
    public function updateAllProgress()
    {
        $projects = Project::all();
        $updated = 0;

        foreach ($projects as $project) {
            $oldProgress = $project->progress;
            $project->updateProgress();
            
            if ($oldProgress != $project->progress) {
                $updated++;
            }
        }

        return response()->json([
            'message' => "Fortschritt für {$updated} Projekte aktualisiert.",
            'updated_count' => $updated
        ]);
    }

    /**
     * Gibt Statistiken für das Dashboard zurück
     */
    public function getStatistics(Request $request)
    {
        $period = $request->get('period', 'week'); // week, month, year
        
        switch ($period) {
            case 'month':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;
            case 'year':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
            case 'week':
            default:
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
        }

        $timeEntries = TimeEntry::whereBetween('date', [$startDate, $endDate]);

        $statistics = [
            'total_hours' => $timeEntries->sum('hours'),
            'billable_hours' => $timeEntries->where('billable', true)->sum('hours'),
            'non_billable_hours' => $timeEntries->where('billable', false)->sum('hours'),
            'unique_employees' => $timeEntries->distinct('employee_id')->count(),
            'unique_projects' => $timeEntries->distinct('project_id')->count(),
            'average_hours_per_day' => $timeEntries->count() > 0 ? 
                $timeEntries->sum('hours') / $timeEntries->distinct('date')->count() : 0
        ];

        return response()->json($statistics);
    }
}