<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Sync\Jobs\SyncProjects;
use App\Services\Moco\ProjectsService;

final class SyncProjectsCommand extends Command
{
    protected $signature = 'moco:sync-projects {--full : Full sync}';
    protected $description = 'Sync projects from MOCO to local DB';

    public function handle(): int
    {
        $full = (bool) $this->option('full');

        // synchron ausführen, kein Queue-Worker nötig
        (new SyncProjects($full))->handle(app(ProjectsService::class));

        $this->info($full ? 'Full sync done.' : 'Incremental sync done.');
        return self::SUCCESS;
    }
}
