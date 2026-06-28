<?php

namespace App\Console\Commands;

use App\Services\DesktopSyncOrchestrator;
use App\Support\DesktopDatabase;
use Illuminate\Console\Command;

class SyncDesktopDataCommand extends Command
{
    protected $signature = 'pathology:sync-desktop';

    protected $description = 'Sync tests and test particulars from desktop SQL Server into the web database';

    public function handle(DesktopSyncOrchestrator $orchestrator): int
    {
        if (!DesktopDatabase::isEnabled()) {
            $this->warn(DesktopDatabase::getDisabledReason() ?? 'Desktop sync is disabled.');

            return self::FAILURE;
        }

        $this->info('Syncing tests & particulars from desktop SQL Server...');

        $results = $orchestrator->syncAll();

        foreach ($results as $entity => $stats) {
            if (isset($stats['error'])) {
                $this->error(ucfirst($entity) . ': ' . $stats['error']);
                continue;
            }

            $this->line(sprintf(
                '%s — processed: %d, created/inserted: %d, updated: %d, deactivated: %d',
                ucfirst(str_replace('_', ' ', $entity)),
                $stats['processed'] ?? $stats['total'] ?? 0,
                $stats['inserted'] ?? $stats['created'] ?? 0,
                $stats['updated'] ?? 0,
                $stats['deactivated'] ?? 0
            ));

            if (isset($stats['source'])) {
                $this->line("  Source: {$stats['source']}");
            }
        }

        $this->info('Done. Lab bookings are read live from desktop view on lookup (not synced).');

        return self::SUCCESS;
    }
}
