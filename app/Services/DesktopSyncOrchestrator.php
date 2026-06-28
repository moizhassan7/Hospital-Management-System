<?php

namespace App\Services;

use App\Support\DesktopDatabase;
use Illuminate\Support\Facades\Log;

/**
 * Syncs test master data from SQL Server into the web database.
 *
 * Lab bookings/patients are NOT synced — they are read live from Leb_reg_test_info on lookup.
 */
class DesktopSyncOrchestrator
{
    public function __construct(
        private DesktopTestCatalogSyncService $testCatalogSync,
        private DesktopTestParticularSyncService $particularSync
    ) {}

    /**
     * @return array<string, array<string, int|string|null>>
     */
    public function syncAll(): array
    {
        if (!DesktopDatabase::isEnabled()) {
            $reason = DesktopDatabase::getDisabledReason() ?? 'Desktop sync disabled';

            Log::warning('Desktop sync skipped: ' . $reason);

            return ['status' => ['error' => $reason]];
        }

        $results = [];

        try {
            $results['tests'] = $this->testCatalogSync->syncAll(true);
        } catch (\Throwable $e) {
            $results['tests'] = ['error' => $e->getMessage()];
            Log::error('Test catalog sync failed: ' . $e->getMessage());
        }

        try {
            $results['test_particulars'] = $this->particularSync->syncAll();
        } catch (\Throwable $e) {
            $results['test_particulars'] = ['error' => $e->getMessage()];
            Log::error('Test particulars sync failed: ' . $e->getMessage());
        }

        return $results;
    }
}
