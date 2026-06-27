<?php

namespace Database\Seeders;

use App\Services\DesktopTestCatalogSyncService;
use Illuminate\Database\Seeder;

class DesktopTestCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $sync = app(DesktopTestCatalogSyncService::class);
        $result = $sync->syncAll(true);

        $this->command?->info(sprintf(
            'Desktop test catalog synced (%s): %d tests (%d new, %d updated)',
            $result['source'] ?? 'unknown',
            $result['total'],
            $result['created'],
            $result['updated']
        ));

        if (($result['created'] ?? 0) > 0 || ($result['updated'] ?? 0) > 0) {
            $this->call(TestParticularsFromExcelSeeder::class);
            $this->call(PathologyPanelSeeder::class);
        }
    }
}
