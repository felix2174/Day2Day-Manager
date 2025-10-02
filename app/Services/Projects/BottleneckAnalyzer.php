<?php
declare(strict_types=1);

namespace App\Services\Projects;

use App\Models\Project;

final class BottleneckAnalyzer
{
    /**
     * Kombiniert Team-, Budget-, Timeline- und Progress-Signale zu Engpässen.
     */
    public function analyze(
        Project $p,
        array $team = [],
        array $budget = [],
        ?array $timeline = null,
        array $progress = []
    ): array {
        $issues = [];

        // 1) Überlastete Teammitglieder
        if (!empty($team['overloaded_members'])) {
            foreach ($team['overloaded_members'] as $m) {
                $sev = ($m['utilization'] ?? 0) >= 120 ? 'critical' : 'warning';
                $issues[] = [
                    'type' => 'team_overload',
                    'severity' => $sev,
                    'message' => "{$m['name']} overloaded (" . round($m['utilization']) . "%)",
                ];
            }
        }

        // 2) Budget fast/über verbraucht
        if (!empty($budget)) {
            $util = (float) ($budget['budget_utilization'] ?? 0);
            if ($util > 90) {
                $issues[] = [
                    'type' => 'budget',
                    'severity' => $util > 100 ? 'critical' : 'warning',
                    'message' => "Budget usage " . round($util, 1) . "%", 
                ];
            }
        }

        // 3) Timeline überfällig oder kurz vor Ende
        if ($timeline) {
            if (($timeline['phase'] ?? '') === 'finished' && ($timeline['overdue_days'] ?? 0) > 0) {
                $issues[] = [
                    'type' => 'timeline_overdue',
                    'severity' => 'critical',
                    'message' => "Overdue by {$timeline['overdue_days']} days",
                ];
            } elseif (($timeline['phase'] ?? '') === 'running' && ($timeline['days_remaining'] ?? 0) <= 3) {
                $issues[] = [
                    'type' => 'timeline_risk',
                    'severity' => 'warning',
                    'message' => "Only {$timeline['days_remaining']} days remaining",
                ];
            }
        } else {
            $issues[] = [
                'type' => 'timeline_missing',
                'severity' => 'warning',
                'message' => 'Start or end date missing',
            ];
        }

        // 4) Progress-Divergenz: Zeitfortschritt >> Arbeitsfortschritt
        if (!empty($progress) && $timeline) {
            $timeP = (float) ($timeline['time_progress'] ?? 0);
            $workP = (float) ($progress['hours'] ?? 0);
            if ($timeP - $workP >= 25) {
                $issues[] = [
                    'type' => 'progress_gap',
                    'severity' => 'warning',
                    'message' => "Time progress {$timeP}% vs. work {$workP}% (gap ≥ 25%)",
                ];
            }
        }

        // 5) Governance: kein Verantwortlicher, keine Assignments
        if (empty($p->responsible_id)) {
            $issues[] = [
                'type' => 'no_responsible',
                'severity' => 'warning',
                'message' => 'No responsible person set',
            ];
        }
        if (($team['total_assigned'] ?? 0) == 0) {
            $issues[] = [
                'type' => 'no_team_assignment',
                'severity' => 'warning',
                'message' => 'No team assignments',
            ];
        }

        $critical = collect($issues)->where('severity', 'critical')->count();
        $warning  = collect($issues)->where('severity', 'warning')->count();

        return [
            'bottlenecks'    => $issues,
            'critical_count' => $critical,
            'warning_count'  => $warning,
            'total_count'    => count($issues),
        ];
    }
}
