<?php
declare(strict_types=1);

namespace App\Services\Projects;

use App\Models\Project;

final class TeamAnalysisService
{
    public function summary(Project $p): array
    {
        $assignments = $p->assignments()->with('employee')->get();

        $members = [];
        $totalCapacity = 0.0;
        $totalAssigned = 0.0;

        foreach ($assignments as $a) {
            $e   = $a->employee;
            $cap = (float) ($e->weekly_capacity ?? 0);
            $ass = (float) ($a->weekly_hours ?? 0);
            $util = $cap > 0 ? ($ass / $cap) * 100.0 : 0.0;

            $members[] = [
                'id'            => $e->id,
                'name'          => trim(($e->first_name ?? '').' '.($e->last_name ?? '')),
                'weekly_hours'  => $ass,
                'capacity'      => $cap,
                'utilization'   => $util,
                'free_capacity' => max(0.0, $cap - $ass),
            ];

            $totalCapacity += $cap;
            $totalAssigned += $ass;
        }

        $teamUtil = $totalCapacity > 0 ? ($totalAssigned / $totalCapacity) * 100.0 : 0.0;

        return [
            'team_utilization'    => $teamUtil,
            'total_capacity'      => $totalCapacity,
            'total_assigned'      => $totalAssigned,
            'overloaded_members'  => collect($members)->where('utilization', '>', 100)->values()->all(),
            'underutilized_count' => collect($members)->where('utilization', '<', 80)->count(),
            'members'             => $members,
        ];
    }
}
