<?php

namespace App\Services;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EmployeeKpiService
{
    private const CACHE_TTL_MINUTES = 30;

    public function __construct(private readonly MocoService $mocoService)
    {
    }

    /**
     * Enriches employees with KPI data retrieved from MOCO and returns warnings.
     */
    public function enrich(Collection $employees): array
    {
        $warnings = [];
        $now = Carbon::now();
        $fourWeekStart = $now->copy()->subDays(28);
        $twelveWeekStart = $now->copy()->subDays(84);
        $statusCounts = [
            'critical' => 0,
            'warning' => 0,
            'balanced' => 0,
            'underutilized' => 0,
            'unknown' => 0,
        ];

        foreach ($employees as $employee) {
            $employee->kpi_available = false;
            $employee->kpi_hours_4w = 0.0;
            $employee->kpi_hours_12w = 0.0;
            $employee->kpi_util_4w = 0;
            $employee->kpi_util_12w = 0;
            $employee->kpi_status_4w = 'unknown';
            $employee->kpi_status_12w = 'unknown';
            $employee->kpi_absence_alert = false;
            $employee->kpi_absence_summary = null;
            $employee->kpi_top_project = null;
            $employee->kpi_bottleneck = false;
            $employee->moco_weekly_capacity = $employee->weekly_capacity ?? 40;

            if (!$employee->moco_id) {
                $warnings[] = "Keine MOCO-ID für {$employee->first_name} {$employee->last_name} hinterlegt.";
                continue;
            }

            try {
                $metrics = Cache::remember(
                    'moco:employee_metrics:' . $employee->moco_id,
                    now()->addMinutes(self::CACHE_TTL_MINUTES),
                    function () use ($employee, $fourWeekStart, $twelveWeekStart, $now) {
                        return $this->buildMetrics($employee, $fourWeekStart, $twelveWeekStart, $now);
                    }
                );

                if (!$metrics) {
                    $warnings[] = "MOCO-Kennzahlen für {$employee->first_name} {$employee->last_name} derzeit nicht verfügbar.";
                    continue;
                }

                $employee->kpi_available = true;
                $employee->moco_weekly_capacity = $metrics['weekly_capacity'];
                $employee->kpi_hours_4w = $metrics['hours_4w'];
                $employee->kpi_hours_12w = $metrics['hours_12w'];
                $employee->kpi_util_4w = $metrics['util_4w'];
                $employee->kpi_util_12w = $metrics['util_12w'];
                $employee->kpi_status_4w = $metrics['status_4w'];
                $employee->kpi_status_12w = $metrics['status_12w'];
                $employee->kpi_absence_alert = $metrics['absence_alert'];
                $employee->kpi_absence_summary = $metrics['absence_summary'];
                $employee->kpi_top_project = $metrics['top_project'];
                $employee->kpi_bottleneck = $metrics['bottleneck'];
                $statusCounts[$metrics['status_4w']] = ($statusCounts[$metrics['status_4w']] ?? 0) + 1;
            } catch (\Throwable $e) {
                Log::warning('Employee KPI sync failed', [
                    'employee_id' => $employee->id,
                    'moco_id' => $employee->moco_id,
                    'message' => $e->getMessage(),
                ]);
                $warnings[] = "MOCO-Kennzahlen für {$employee->first_name} {$employee->last_name} konnten nicht geladen werden.";
            }
        }

        return [
            'employees' => $employees,
            'warnings' => array_unique($warnings),
            'status_counts' => $statusCounts,
        ];
    }

    private function buildMetrics(Employee $employee, Carbon $fourWeekStart, Carbon $twelveWeekStart, Carbon $now): ?array
    {
        $mocoUser = $this->mocoService->getUser($employee->moco_id);
        if (!is_array($mocoUser)) {
            return null;
        }

        $weeklyCapacity = $this->resolveWeeklyCapacity($employee, $mocoUser);

        $activities = $this->mocoService->getUserActivities($employee->moco_id, [
            'from' => $twelveWeekStart->format('Y-m-d'),
            'to' => $now->format('Y-m-d'),
            'limit' => 500,
        ]);

        if (!is_array($activities)) {
            $activities = [];
        }

        $hours4 = 0.0;
        $hours12 = 0.0;
        $projectHours4 = [];

        foreach ($activities as $activity) {
            $hours = (float) ($activity['hours'] ?? 0);
            if ($hours <= 0) {
                continue;
            }

            $dateString = $activity['date'] ?? null;
            if (!$dateString) {
                continue;
            }

            $activityDate = Carbon::parse($dateString);
            if ($activityDate->lt($twelveWeekStart)) {
                continue;
            }

            $hours12 += $hours;

            if ($activityDate->gte($fourWeekStart)) {
                $hours4 += $hours;

                $projectName = $activity['project']['name'] ?? 'Ohne Projekt';
                if (!isset($projectHours4[$projectName])) {
                    $projectHours4[$projectName] = 0.0;
                }
                $projectHours4[$projectName] += $hours;
            }
        }

        $util4 = $this->calculateUtilization($hours4, $weeklyCapacity, 28);
        $util12 = $this->calculateUtilization($hours12, $weeklyCapacity, 84);

        $status4 = $this->determineStatus($util4);
        $status12 = $this->determineStatus($util12);

        $topProject = null;
        if ($hours4 > 0 && !empty($projectHours4)) {
            arsort($projectHours4);
            $name = array_key_first($projectHours4);
            $topHours = $projectHours4[$name];
            $topProject = [
                'name' => $name,
                'hours' => round($topHours, 1),
                'share' => round(($topHours / $hours4) * 100, 1),
            ];
        }

        $absenceData = $this->resolveAbsenceAlert($employee->moco_id, $now);

        $bottleneck = $status12 === 'critical' || $status4 === 'critical' || $absenceData['alert'];

        return [
            'weekly_capacity' => $weeklyCapacity,
            'hours_4w' => round($hours4, 1),
            'hours_12w' => round($hours12, 1),
            'util_4w' => $util4,
            'util_12w' => $util12,
            'status_4w' => $status4,
            'status_12w' => $status12,
            'top_project' => $topProject,
            'absence_alert' => $absenceData['alert'],
            'absence_summary' => $absenceData['summary'],
            'bottleneck' => $bottleneck,
        ];
    }

    private function resolveWeeklyCapacity(Employee $employee, ?array $mocoUser): float
    {
        $fallback = (float) ($employee->weekly_capacity ?? 40);
        if (!$mocoUser) {
            return $fallback;
        }

        if (isset($mocoUser['work_schedule']) && is_array($mocoUser['work_schedule'])) {
            $sum = 0.0;
            foreach ($mocoUser['work_schedule'] as $hours) {
                $sum += (float) $hours;
            }
            if ($sum > 0) {
                return $sum;
            }
        }

        if (isset($mocoUser['work_hours_per_week']) && $mocoUser['work_hours_per_week'] > 0) {
            return (float) $mocoUser['work_hours_per_week'];
        }

        if (isset($mocoUser['custom_properties']) && is_array($mocoUser['custom_properties'])) {
            foreach ($mocoUser['custom_properties'] as $key => $value) {
                if (!is_scalar($value)) {
                    continue;
                }
                $normalizedKey = strtolower((string) $key);
                if (in_array($normalizedKey, ['wochenkapazität', 'weekly_capacity', 'weeklyhours'], true)) {
                    $capacity = (float) $value;
                    if ($capacity > 0) {
                        return $capacity;
                    }
                }
            }
        }

        return $fallback;
    }

    private function calculateUtilization(float $hours, float $weeklyCapacity, int $days): int
    {
        if ($weeklyCapacity <= 0) {
            return 0;
        }

        $weeks = $days / 7;
        $maxHours = $weeklyCapacity * $weeks;
        if ($maxHours <= 0) {
            return 0;
        }

        return (int) round(($hours / $maxHours) * 100);
    }

    private function determineStatus(int $utilization): string
    {
        return match (true) {
            $utilization >= 110 => 'critical',
            $utilization >= 90 => 'warning',
            $utilization >= 70 => 'balanced',
            default => 'underutilized',
        };
    }

    private function resolveAbsenceAlert(int $mocoUserId, Carbon $now): array
    {
        try {
            $absences = $this->mocoService->getUserAbsences($mocoUserId, [
                'from' => $now->format('Y-m-d'),
                'to' => $now->copy()->addDays(60)->format('Y-m-d'),
                'limit' => 200,
            ]);
        } catch (\Throwable $e) {
            Log::warning('MOCO absence lookup failed', [
                'user_id' => $mocoUserId,
                'message' => $e->getMessage(),
            ]);
            return ['alert' => false, 'summary' => null];
        }

        if (!is_array($absences) || empty($absences)) {
            return ['alert' => false, 'summary' => null];
        }

        $alert = false;
        $summary = null;

        foreach ($absences as $absence) {
            $start = isset($absence['start_date']) ? Carbon::parse($absence['start_date']) : null;
            $end = isset($absence['end_date']) ? Carbon::parse($absence['end_date']) : null;
            if (!$start || !$end) {
                continue;
            }

            $duration = $start->diffInDays($end) + 1;
            if ($duration >= 5) {
                $alert = true;
                $type = $absence['type'] ?? 'Abwesenheit';
                $summary = sprintf(
                    '%s vom %s bis %s (%d Tage)',
                    ucfirst($type),
                    $start->format('d.m.'),
                    $end->format('d.m.'),
                    $duration
                );
                break;
            }
        }

        return ['alert' => $alert, 'summary' => $summary];
    }
}
