<?php

namespace App\Console\Commands;

use App\Services\DesktopTestCatalogSyncService;
use Illuminate\Console\Command;

class SyncDesktopTestsCommand extends Command
{
    protected $signature = 'pathology:sync-desktop-tests {--file : Force sync from data_from_desktop_test.txt}';

    protected $description = 'Sync pathology test catalog from Desktop SQL Server (same IDs as desktop)';

    public function handle(DesktopTestCatalogSyncService $syncService): int
    {
        $this->info('Syncing desktop pathology tests...');

        $result = $this->option('file')
            ? $syncService->syncAll(false)
            : $syncService->syncAll(true);

        if ($syncService->getLastError()) {
            $this->warn($syncService->getLastError());
        }

        $this->info(sprintf(
            'Done via %s — total: %d, created: %d, updated: %d',
            $result['source'] ?? 'unknown',
            $result['total'],
            $result['created'],
            $result['updated']
        ));

        return self::SUCCESS;
    }
}
