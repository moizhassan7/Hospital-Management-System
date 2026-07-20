<?php

namespace Database\Seeders;

use App\Models\CollectionCenter;
use App\Models\Organization;
use App\Models\User;
use App\Services\Lims\LabNumberAllocator;
use App\Services\Lims\MrNumberAllocator;
use Illuminate\Database\Seeder;

/**
 * Seeds a default org, Main Lab, one spoke CC, and MR sequence for local LIMS dev.
 */
class LimsOrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::query()->firstOrCreate(
            ['code' => 'MMC'],
            [
                'name' => 'Madina Medical Complex',
                'timezone' => 'Asia/Karachi',
            ]
        );

        $mainLab = CollectionCenter::query()->firstOrCreate(
            [
                'organization_id' => $org->id,
                'code' => 'MAIN',
            ],
            [
                'name' => 'Main Laboratory',
                'kind' => CollectionCenter::KIND_MAIN_LAB,
                'lab_number_prefix' => 'MAIN',
                'is_active' => true,
            ]
        );

        CollectionCenter::query()->firstOrCreate(
            [
                'organization_id' => $org->id,
                'code' => 'CC1',
            ],
            [
                'name' => 'Collection Center 1',
                'kind' => CollectionCenter::KIND_COLLECTION_CENTER,
                'lab_number_prefix' => 'CC1',
                'is_active' => true,
            ]
        );

        app(MrNumberAllocator::class)->ensureSequence($org->id, 1);

        $labAllocator = app(LabNumberAllocator::class);
        $yearMonth = now('Asia/Karachi')->format('Ym');
        $labAllocator->ensureSequence($mainLab->id, $yearMonth, 1);

        $cc1 = CollectionCenter::query()
            ->where('organization_id', $org->id)
            ->where('code', 'CC1')
            ->first();
        if ($cc1) {
            $labAllocator->ensureSequence($cc1->id, $yearMonth, 1);
        }

        // Attach existing admin / lab users to the org as Main Lab scope (no CC).
        User::query()
            ->whereNull('organization_id')
            ->update([
                'organization_id' => $org->id,
                'user_scope' => User::SCOPE_MAIN_LAB,
                'collection_center_id' => null,
            ]);

        $this->command?->info(sprintf(
            'LIMS org seeded: %s (id=%d), Main Lab id=%d, MR + lab sequences ready for %s.',
            $org->code,
            $org->id,
            $mainLab->id,
            $yearMonth
        ));
    }
}
