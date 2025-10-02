<?php
declare(strict_types=1);

namespace App\Services\Projects;

use App\Models\Project;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

final class TimelineAnalysisService
{
    public function summary(Project $p): ?array
    {
        if (!$p->start_date || !$p->end_date) {
            return null;
        }

        $start = Carbon::parse($p->start_date);
        $end   = Carbon::parse($p->end_date);
        $now   = Carbon::now();

        $totalDays = max(1, $start->diffInDays($end));
        $phase = 'running';
        $timeProgress = 0.0;
        $daysToStart = 0;
        $daysRemaining = 0;
        $overdueDays = 0;

        if ($now->lt($start)) {
            $phase = 'planned';
            $timeProgress = 0.0;
            $daysToStart = $now->diffInDays($start);
        } elseif ($now->gt($end)) {
            $phase = 'finished';
            $timeProgress = 100.0;
            $overdueDays = $end->diffInDays($now);
        } else {
            $phase = 'running';
            $passed = $start->diffInDays($now);
            $daysRemaining = $now->diffInDays($end);
            $timeProgress = min(100.0, ($passed / $totalDays) * 100.0);
        }

        return [
            'phase'           => $phase,
            'start_date'      => $start,
            'end_date'        => $end,
            'now'             => $now,
            'total_days'      => $totalDays,
            'time_progress'   => round($timeProgress, 1),
            'days_to_start'   => $daysToStart,
            'days_remaining'  => $daysRemaining,
            'overdue_days'    => $overdueDays,
        ];
    }

    public function cached(Project $p, int $ttl = 60): ?array
    {
        $key = "project:timeline:{$p->id}:{$p->updated_at}";
        return Cache::remember($key, $ttl, fn () => $this->summary($p));
    }
}
