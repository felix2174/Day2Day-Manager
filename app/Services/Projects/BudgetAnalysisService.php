<?php
declare(strict_types=1);

namespace App\Services\Projects;

use App\Models\Project;
use Illuminate\Support\Facades\Cache;

final class BudgetAnalysisService
{
    public function details(Project $p): array
    {
        $totalBudget   = (float) ($p->budget ?? 0);
        $hourlyRate    = (float) ($p->hourly_rate ?? 0);
        $estimatedHrs  = (float) ($p->estimated_hours ?? 0);

        // Wenn Budget vorhanden und Stundensatz gesetzt, aus Budget Stunden schätzen
        if ($totalBudget > 0 && $hourlyRate > 0 && $estimatedHrs === 0.0) {
            $estimatedHrs = $totalBudget / $hourlyRate;
        }

        $actualHours = (float) $p->timeEntries()->sum('hours');
        $actualCost  = $hourlyRate > 0 ? $actualHours * $hourlyRate : 0.0;

        $utilization = $totalBudget > 0 ? ($actualCost / $totalBudget) * 100.0 : 0.0;

        return [
            'total_budget'       => $totalBudget,
            'hourly_rate'        => $hourlyRate,
            'estimated_hours'    => $estimatedHrs,
            'actual_hours'       => $actualHours,
            'actual_cost'        => $actualCost,
            'remaining_budget'   => max(0, $totalBudget - $actualCost),
            'budget_utilization' => $utilization, // %
        ];
    }

    public function cached(Project $p, int $ttl = 60): array
    {
        $key = "project:budget:{$p->id}:{$p->updated_at}";
        return Cache::remember($key, $ttl, fn () => $this->details($p));
    }
}
