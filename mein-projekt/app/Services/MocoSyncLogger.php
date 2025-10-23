<?php

namespace App\Services;

use App\Models\MocoSyncLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MocoSyncLogger
{
    protected ?MocoSyncLog $currentLog = null;

    /**
     * Start a new sync log
     */
    public function start(string $syncType, array $parameters = []): MocoSyncLog
    {
        $this->currentLog = MocoSyncLog::create([
            'sync_type' => $syncType,
            'status' => 'started',
            'parameters' => $parameters,
            'started_at' => Carbon::now(),
            'user_id' => Auth::id(),
        ]);

        return $this->currentLog;
    }

    /**
     * Update sync progress
     */
    public function updateProgress(int $processed, int $created, int $updated, int $skipped): void
    {
        if (!$this->currentLog) {
            return;
        }

        $this->currentLog->update([
            'items_processed' => $processed,
            'items_created' => $created,
            'items_updated' => $updated,
            'items_skipped' => $skipped,
        ]);
    }

    /**
     * Mark sync as completed successfully
     */
    public function complete(int $processed, int $created, int $updated, int $skipped): void
    {
        if (!$this->currentLog) {
            return;
        }

        $completedAt = Carbon::now();
        $duration = max(0, $completedAt->diffInSeconds($this->currentLog->started_at, false));

        $this->currentLog->update([
            'status' => 'completed',
            'items_processed' => $processed,
            'items_created' => $created,
            'items_updated' => $updated,
            'items_skipped' => $skipped,
            'completed_at' => $completedAt,
            'duration_seconds' => $duration,
        ]);

        $this->currentLog = null;
    }

    /**
     * Mark sync as failed
     */
    public function fail(string $errorMessage): void
    {
        if (!$this->currentLog) {
            return;
        }

        $completedAt = Carbon::now();
        $duration = max(0, $completedAt->diffInSeconds($this->currentLog->started_at, false));

        $this->currentLog->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'completed_at' => $completedAt,
            'duration_seconds' => $duration,
        ]);

        $this->currentLog = null;
    }

    /**
     * Get current log instance
     */
    public function getCurrentLog(): ?MocoSyncLog
    {
        return $this->currentLog;
    }

    /**
     * Set current log (for restoring state)
     */
    public function setCurrentLog(MocoSyncLog $log): void
    {
        $this->currentLog = $log;
    }
}

