<?php
declare(strict_types=1);

namespace App\Services\Projects;

use App\Models\Project;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

final class ProjectProgressService
{
    public function automatic(Project $project): float
    {
        return $this->details($project)['automatic'];
    }

    public function details(Project $project): array
    {
        $estimated = (float) ($project->estimated_hours ?? 0);
        $worked    = (float) $project->timeEntries()->sum('hours');

        $hoursProgress = $estimated > 0 ? min(100, ($worked / $estimated) * 100) : 0;

        $start = $project->start_date ? Carbon::parse($project->start_date) : null;
        $end   = $project->end_date   ? Carbon::parse($project->end_date)   : null;
        $now   = Carbon::now();

        $timeProgress = 0;
        if ($start && $end) {
            if ($now->lt($start)) {
                $timeProgress = 0;
            } elseif ($now->gte($end)) {
                $timeProgress = 100;
            } else {
                $total  = max(1, $start->diffInDays($end));
                $passed = $start->diffInDays($now);
                $timeProgress = min(100, ($passed / $total) * 100);
            }
        }

        $manual = (float) ($project->progress ?? 0);

        $weights   = ['hours' => 0.4, 'time' => 0.3, 'manual' => 0.3];
        $automatic = $hoursProgress * $weights['hours']
                   + $timeProgress  * $weights['time']
                   + $manual        * $weights['manual'];

        $automatic = round(max(0, min(100, $automatic)), 1);

        return [
            'automatic'           => $automatic,
            'hours'               => $hoursProgress,
            'time'                => $timeProgress,
            'manual'              => $manual,
            'total_hours_worked'  => $worked,
            'estimated_hours'     => $estimated,
            'hours_remaining'     => max(0, $estimated - $worked),
        ];
    }

    public function cachedDetails(Project $project, int $ttl = 60): array
    {
        $key = "project:progress:{$project->id}:{$project->updated_at}";
        return Cache::remember($key, $ttl, fn () => $this->details($project));
    }

    public function refreshAndPersist(Project $project): void
    {
        $project->progress = $this->automatic($project);
        $project->save();
    }
}
