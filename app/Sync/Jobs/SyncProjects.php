<?php
declare(strict_types=1);

namespace App\Sync\Jobs;

use App\Models\Project;
use App\Services\Moco\ProjectsService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

final class SyncProjects implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly bool $full = false) {}

    public function handle(ProjectsService $api): void
    {
        $since = null;
        if (!$this->full) {
            $hours = (int) config('moco.sync_since_default_hours', 24);
            $since = Cache::get('sync:projects:last_since')
                ? Carbon::parse((string) Cache::get('sync:projects:last_since'))
                : now()->subHours($hours);
        }

        $rows = [];
        foreach ($api->listAll($since) as $p) {
            $rows[] = $this->map($p);
            if (count($rows) === 500) {
                $this->upsert($rows);
                $rows = [];
            }
        }
        if ($rows) {
            $this->upsert($rows);
        }

        Cache::put('sync:projects:last_since', now()->toIso8601ZuluString(), now()->addDays(7));
    }

    private function upsert(array $rows): void
    {
        if (!$rows) return;

        Project::upsert(
            $rows,
            ['moco_id'],                 // Unique-Schlüssel
            array_diff(array_keys($rows[0]), ['moco_id']) // zu aktualisierende Spalten
        );
    }

    private function map(array $p): array
    {
        $start = $p['start_date'] ?? $p['starts_at'] ?? $p['starts_on'] ?? null;
        $end   = $p['end_date']   ?? $p['ends_at']   ?? $p['ends_on']   ?? null;

        return [
            'moco_id'     => (int)   ($p['id'] ?? 0),
            'name'        => (string)($p['name'] ?? ''),
            'description' => $p['description'] ?? null,
            'identifier'  => $p['identifier']  ?? null,
            'status'      => $p['status']      ?? null,
            'start_date'  => $start ? Carbon::parse($start)->toDateString() : null,
            'end_date'    => $end   ? Carbon::parse($end)->toDateString()   : null,
            'hourly_rate' => isset($p['hourly_rate']) ? (float)$p['hourly_rate'] : null,
            'budget'      => isset($p['budget']) ? (float)$p['budget'] : null,
            'billable'    => isset($p['billable']) ? (bool)$p['billable'] : null,
        ];
    }
}
