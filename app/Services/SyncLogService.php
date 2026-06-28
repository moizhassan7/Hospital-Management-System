<?php

namespace App\Services;

use App\Models\SyncLog;
use Illuminate\Support\Facades\Log;

class SyncLogService
{
    public function start(string $entity): SyncLog
    {
        return SyncLog::create([
            'entity' => $entity,
            'started_at' => now(),
            'status' => SyncLog::STATUS_RUNNING,
        ]);
    }

    /**
     * @param  array<string, mixed>  $stats
     */
    public function finishSuccess(SyncLog $log, array $stats = [], ?array $metadata = null): SyncLog
    {
        $log->update([
            'finished_at' => now(),
            'status' => SyncLog::STATUS_SUCCESS,
            'records_processed' => (int) ($stats['processed'] ?? 0),
            'records_inserted' => (int) ($stats['inserted'] ?? 0),
            'records_updated' => (int) ($stats['updated'] ?? 0),
            'records_deactivated' => (int) ($stats['deactivated'] ?? 0),
            'metadata' => $metadata,
        ]);

        Log::info('Desktop sync completed', [
            'entity' => $log->entity,
            'processed' => $log->records_processed,
            'inserted' => $log->records_inserted,
            'updated' => $log->records_updated,
        ]);

        return $log->fresh();
    }

    public function finishFailure(SyncLog $log, string $message, ?array $metadata = null): SyncLog
    {
        $log->update([
            'finished_at' => now(),
            'status' => SyncLog::STATUS_FAILED,
            'error_message' => $message,
            'metadata' => $metadata,
        ]);

        Log::error('Desktop sync failed', [
            'entity' => $log->entity,
            'error' => $message,
        ]);

        return $log->fresh();
    }
}
