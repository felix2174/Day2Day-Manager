<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Services\Projects\ProjectProgressService;

class Project extends Model
{
    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
        'estimated_hours',
        'hourly_rate',
        'progress',
        'responsible_id',
        'moco_id',
        'identifier',
        'billable',
        'fixed_price',
        'retainer',
        'budget',
        'budget_monthly',
        'budget_expenses',
        'currency',
        'billing_variant',
        'billing_address',
        'billing_email_to',
        'billing_email_cc',
        'billing_notes',
        'color',
        'customer_id',
        'leader_id',
        'co_leader_id',
        'deal_id',
        'project_group_id',
        'billing_contact_id',
        'contact_id',
        'secondary_contact_id',
        'finish_date',
        'info',
        'setting_include_time_report',
        'customer_report_url',
        'archived_on',
        'created_at',
        'updated_at',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'billable' => 'boolean',
        'fixed_price' => 'boolean',
        'retainer' => 'boolean',
        'budget' => 'decimal:2',
        'budget_monthly' => 'decimal:2',
        'budget_expenses' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'setting_include_time_report' => 'boolean',
        'archived_on' => 'datetime',
        'finish_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ───────────────────────────── Relationships ─────────────────────────────

    /** Ein Projekt hat viele Zuweisungen. */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /** Ein Projekt kann mehreren Teams zugewiesen werden. */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_assignments');
    }

    /** Verantwortlicher (Mitarbeiter). */
    public function responsible(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'responsible_id');
    }

    /** Ein Projekt hat viele Zeiteinträge. */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    // ───────────────────── Progress-Delegation an Service ────────────────────

    /**
     * @deprecated Logik liegt im ProjectProgressService::automatic().
     */
    public function calculateAutomaticProgress(): float
    {
        return app(ProjectProgressService::class)->automatic($this);
    }

    /**
     * @deprecated Logik liegt im ProjectProgressService::refreshAndPersist().
     */
    public function updateProgress(): void
    {
        app(ProjectProgressService::class)->refreshAndPersist($this);
    }

    /**
     * @deprecated Logik liegt im ProjectProgressService::details().
     */
    public function getProgressDetails(): array
    {
        return app(ProjectProgressService::class)->details($this);
    }

    // ─────────────── Verbleibende Analyse-Methoden (wird migriert) ───────────
    // Hinweis: Diese Methoden bleiben vorerst, werden in nächsten Schritten
    // in dedizierte Services ausgelagert (Budget/Team/Timeline/Bottlenecks).

    /**
     * Analysiert MOCO-Projektdaten für Budget und Team-Performance.
     * @deprecated Wird in Services ausgelagert.
     */
    public function analyzeMocoData(): array
    {
        return [
            'budget' => $this->getBudgetAnalysis(),
            'team' => $this->getTeamAnalysis(),
            'timeline' => $this->getTimelineAnalysis(),
            'bottlenecks' => $this->getBottleneckAnalysis(),
        ];
    }

    /**
     * Budget-Analyse basierend auf Projektfeldern und TimeEntries.
     * @deprecated Wird in BudgetAnalysisService ausgelagert.
     */
    private function getBudgetAnalysis(): array
    {
        $totalBudget = 0;
        $hourlyBudget = 0;
        $fixedBudget = 0;
        $estimatedHours = 0;

        if ($this->budget) {
            $totalBudget += (float) $this->budget;
        }

        if ($this->budget && $this->hourly_rate) {
            $estimatedHours = (float) $this->budget / (float) $this->hourly_rate;
            $hourlyBudget = (float) $this->budget;
        }

        $actualHours = (float) $this->timeEntries()->sum('hours');
        $actualCost = $actualHours * (float) ($this->hourly_rate ?? 0);

        $util = $totalBudget > 0 ? ($actualCost / $totalBudget) * 100 : 0;

        return [
            'total_budget' => $totalBudget,
            'hourly_budget' => $hourlyBudget,
            'fixed_budget' => $fixedBudget,
            'estimated_hours' => $estimatedHours,
            'actual_hours' => $actualHours,
            'actual_cost' => $actualCost,
            'remaining_budget' => max(0, $totalBudget - $actualCost),
            'budget_utilization' => $util,
            'hourly_rate' => (float) ($this->hourly_rate ?? 0),
        ];
    }

    /**
     * Team-Analyse basierend auf Zuweisungen.
     * @deprecated Wird in TeamAnalysisService ausgelagert.
     */
    private function getTeamAnalysis(): array
    {
        $assignments = $this->assignments()->with('employee')->get();
        $teamMembers = [];
        $totalCapacity = 0;
        $totalAssigned = 0;

        foreach ($assignments as $assignment) {
            $employee = $assignment->employee;
            $hoursWorked = (float) $this->timeEntries()
                ->where('employee_id', $employee->id)
                ->sum('hours');

            $utilization = ($employee->weekly_capacity ?? 0) > 0
                ? ((float) $assignment->weekly_hours / (float) $employee->weekly_capacity) * 100
                : 0;

            $teamMembers[] = [
                'employee' => $employee,
                'weekly_hours' => (float) $assignment->weekly_hours,
                'hours_worked' => $hoursWorked,
                'utilization' => $utilization,
                'capacity' => (float) ($employee->weekly_capacity ?? 0),
                'free_capacity' => max(0, (float) ($employee->weekly_capacity ?? 0) - (float) $assignment->weekly_hours),
            ];

            $totalCapacity += (float) ($employee->weekly_capacity ?? 0);
            $totalAssigned += (float) $assignment->weekly_hours;
        }

        return [
            'team_members' => $teamMembers,
            'total_capacity' => $totalCapacity,
            'total_assigned' => $totalAssigned,
            'team_utilization' => $totalCapacity > 0 ? ($totalAssigned / $totalCapacity) * 100 : 0,
            'overloaded_members' => collect($teamMembers)->where('utilization', '>', 100)->count(),
            'underutilized_members' => collect($teamMembers)->where('utilization', '<', 80)->count(),
        ];
    }

    /**
     * Timeline-Analyse.
     * @deprecated Wird in TimelineAnalysisService ausgelagert.
     */
    private function getTimelineAnalysis(): ?array
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }

        $start = \Carbon\Carbon::parse($this->start_date);
        $end   = \Carbon\Carbon::parse($this->end_date);
        $now   = \Carbon\Carbon::now();

        $totalDays = max(0, $start->diffInDays($end));
        $passedDays = $start->diffInDays($now);
        $remainingDays = $now->diffInDays($end);

        return [
            'total_days' => $totalDays,
            'passed_days' => $passedDays,
            'remaining_days' => $remainingDays,
            'is_overdue' => $now->gt($end),
            'progress_percentage' => $totalDays > 0 ? min(100, ($passedDays / $totalDays) * 100) : 0,
            'start_date' => $start,
            'end_date' => $end,
            'current_date' => $now,
        ];
    }

    /**
     * Bottleneck-Analyse.
     * @deprecated Wird in BottleneckAnalyzer ausgelagert.
     */
    private function getBottleneckAnalysis(): array
    {
        $bottlenecks = [];
        $teamAnalysis = $this->getTeamAnalysis();
        $timelineAnalysis = $this->getTimelineAnalysis();

        foreach ($teamAnalysis['team_members'] as $member) {
            if ($member['utilization'] > 100) {
                $bottlenecks[] = [
                    'type' => 'overload',
                    'severity' => $member['utilization'] > 120 ? 'critical' : 'warning',
                    'message' => $member['employee']->first_name . ' ' . $member['employee']->last_name
                        . ' ist überlastet (' . round($member['utilization']) . '%)',
                    'employee' => $member['employee'],
                    'utilization' => $member['utilization'],
                ];
            }
        }

        if ($timelineAnalysis && $timelineAnalysis['is_overdue']) {
            $bottlenecks[] = [
                'type' => 'overdue',
                'severity' => 'critical',
                'message' => 'Projekt ist überfällig um ' . abs($timelineAnalysis['remaining_days']) . ' Tage',
                'overdue_days' => abs($timelineAnalysis['remaining_days']),
            ];
        }

        $budgetAnalysis = $this->getBudgetAnalysis();
        if ($budgetAnalysis['budget_utilization'] > 90) {
            $bottlenecks[] = [
                'type' => 'budget',
                'severity' => $budgetAnalysis['budget_utilization'] > 100 ? 'critical' : 'warning',
                'message' => 'Budget zu ' . round($budgetAnalysis['budget_utilization']) . '% ausgeschöpft',
                'utilization' => $budgetAnalysis['budget_utilization'],
            ];
        }

        return [
            'bottlenecks' => $bottlenecks,
            'critical_count' => collect($bottlenecks)->where('severity', 'critical')->count(),
            'warning_count' => collect($bottlenecks)->where('severity', 'warning')->count(),
            'total_count' => count($bottlenecks),
        ];
    }
}
