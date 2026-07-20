<?php

namespace Database\Seeders;

use App\Models\CollectionCenter;
use App\Models\LimsBooking;
use App\Models\LimsBookingItem;
use App\Models\LimsCommissionRule;
use App\Models\LimsDoctor;
use App\Models\LimsPatient;
use App\Models\LimsSample;
use App\Models\LimsTestCategory;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Test;
use App\Models\User;
use App\Services\Lims\CommissionSnapshotService;
use App\Services\Lims\LabNumberAllocator;
use App\Services\Lims\ManifestNumberAllocator;
use App\Services\Lims\MrNumberAllocator;
use App\Support\LabPermissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Rich LIMS demo dataset for manual QA (Hub & Spoke).
 *
 * Idempotent-ish: stable usernames, org/CC codes, doctor codes, barcodes.
 * Does NOT wipe data or call migrate:fresh.
 *
 * Prerequisite (recommended):
 *   php artisan db:seed --class=RolesAndPermissionsSeeder
 *   php artisan db:seed --class=LimsOrganizationSeeder
 *
 * Then:
 *   php artisan db:seed --class=LimsTestingSeeder
 *
 * Test password for all lims_* users: password
 */
class LimsTestingSeeder extends Seeder
{
    public const DEMO_PASSWORD = 'password';

    public function run(): void
    {
        $this->ensureLabPermissionsExist();

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

        $cc1 = CollectionCenter::query()->firstOrCreate(
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

        $cc2 = CollectionCenter::query()->firstOrCreate(
            [
                'organization_id' => $org->id,
                'code' => 'CC2',
            ],
            [
                'name' => 'Collection Center 2',
                'kind' => CollectionCenter::KIND_COLLECTION_CENTER,
                'lab_number_prefix' => 'CC2',
                'is_active' => true,
            ]
        );

        $this->ensureSequences($org, [$mainLab, $cc1, $cc2]);

        $mainUser = $this->upsertScopedUser(
            username: 'lims_main',
            name: 'LIMS Main Lab Admin',
            email: 'lims_main@mmc.test',
            organizationId: $org->id,
            userScope: User::SCOPE_MAIN_LAB,
            collectionCenterId: null,
            permissions: [
                LabPermissions::BOOKING,
                LabPermissions::SAMPLE_COLLECTION,
                LabPermissions::LAB_ATTENDANT,
                LabPermissions::RESULT_ENTRY,
                LabPermissions::RESULT_EDIT,
                LabPermissions::FRONT_DESK_PRINT,
                LabPermissions::CRITICAL_REPORT,
                LabPermissions::SAMPLES_REPORT,
                LabPermissions::FINANCIAL_SUMMARY,
                LabPermissions::COMMISSION_ADMIN,
                LabPermissions::DOCTOR_PAYOUT,
                LabPermissions::COLLECTION_CENTERS,
            ]
        );

        $cc1User = $this->upsertScopedUser(
            username: 'lims_cc1',
            name: 'LIMS CC1 Operator',
            email: 'lims_cc1@mmc.test',
            organizationId: $org->id,
            userScope: User::SCOPE_COLLECTION_CENTER,
            collectionCenterId: $cc1->id,
            permissions: [
                LabPermissions::BOOKING,
                LabPermissions::SAMPLE_COLLECTION,
            ]
        );

        $cc2User = $this->upsertScopedUser(
            username: 'lims_cc2',
            name: 'LIMS CC2 Operator',
            email: 'lims_cc2@mmc.test',
            organizationId: $org->id,
            userScope: User::SCOPE_COLLECTION_CENTER,
            collectionCenterId: $cc2->id,
            permissions: [
                LabPermissions::BOOKING,
                LabPermissions::SAMPLE_COLLECTION,
            ]
        );

        $hematology = LimsTestCategory::query()->firstOrCreate(
            [
                'organization_id' => $org->id,
                'code' => 'HEMA',
            ],
            ['name' => 'Hematology']
        );

        $chemistry = LimsTestCategory::query()->firstOrCreate(
            [
                'organization_id' => $org->id,
                'code' => 'CHEM',
            ],
            ['name' => 'Clinical Chemistry']
        );

        $drAhmed = LimsDoctor::query()->updateOrCreate(
            [
                'organization_id' => $org->id,
                'code' => 'DR-AHMED',
            ],
            [
                'name' => 'Dr Ahmed Khan',
                'phone' => '03001234001',
                'is_active' => true,
            ]
        );

        $drSara = LimsDoctor::query()->updateOrCreate(
            [
                'organization_id' => $org->id,
                'code' => 'DR-SARA',
            ],
            [
                'name' => 'Dr Sara Malik',
                'phone' => '03001234002',
                'is_active' => true,
            ]
        );

        $drBilal = LimsDoctor::query()->updateOrCreate(
            [
                'organization_id' => $org->id,
                'code' => 'DR-BILAL',
            ],
            [
                'name' => 'Dr Bilal Hussain',
                'phone' => '03001234003',
                'is_active' => true,
            ]
        );

        $this->upsertCommissionRule($org->id, $drAhmed->id, $hematology->id, null, [
            'basis' => LimsCommissionRule::BASIS_PERCENT,
            'percent' => 10,
            'amount' => null,
            'priority' => 20,
        ]);

        $this->upsertCommissionRule($org->id, $drSara->id, $chemistry->id, null, [
            'basis' => LimsCommissionRule::BASIS_FIXED,
            'percent' => null,
            'amount' => 150,
            'priority' => 20,
        ]);

        $this->upsertCommissionRule($org->id, $drBilal->id, null, $cc1->id, [
            'basis' => LimsCommissionRule::BASIS_PERCENT,
            'percent' => 5,
            'amount' => null,
            'priority' => 10,
        ]);

        $demoTest = Test::query()->orderBy('id')->first();

        // sync_id is a Postgres uuid column — use stable UUIDs for idempotent re-runs.
        $this->seedDemoCollectedSample(
            org: $org,
            center: $cc1,
            doctor: $drAhmed,
            category: $hematology,
            actor: $cc1User,
            syncId: 'a0000001-c001-4000-8000-000000000001',
            barcode: 'SEED-CC1-DEMO-001',
            patientName: 'Demo Patient CC1',
            demoTest: $demoTest,
        );

        $this->seedDemoCollectedSample(
            org: $org,
            center: $cc2,
            doctor: $drSara,
            category: $chemistry,
            actor: $cc2User,
            syncId: 'a0000001-c002-4000-8000-000000000002',
            barcode: 'SEED-CC2-DEMO-001',
            patientName: 'Demo Patient CC2',
            demoTest: $demoTest,
        );

        $this->command?->info('LimsTestingSeeder complete.');
        $this->command?->table(
            ['Username', 'Password', 'Scope', 'Center'],
            [
                ['lims_main', self::DEMO_PASSWORD, 'main_lab', '—'],
                ['lims_cc1', self::DEMO_PASSWORD, 'collection_center', 'CC1'],
                ['lims_cc2', self::DEMO_PASSWORD, 'collection_center', 'CC2'],
            ]
        );
        $this->command?->info(sprintf(
            'Centers: MAIN id=%d, CC1 id=%d, CC2 id=%d. Demo barcodes: SEED-CC1-DEMO-001, SEED-CC2-DEMO-001 (status=collected).',
            $mainLab->id,
            $cc1->id,
            $cc2->id
        ));
        $this->command?->info(sprintf(
            'Main user id=%d, CC1 user id=%d, CC2 user id=%d.',
            $mainUser->id,
            $cc1User->id,
            $cc2User->id
        ));
    }

    private function ensureLabPermissionsExist(): void
    {
        foreach (LabPermissions::all() as $name) {
            Permission::updateOrCreate(
                ['name' => $name],
                ['group_name' => LabPermissions::GROUP]
            );
        }
    }

    /**
     * @param  list<CollectionCenter>  $centers
     */
    private function ensureSequences(Organization $org, array $centers): void
    {
        app(MrNumberAllocator::class)->ensureSequence($org->id, 1);

        $labAllocator = app(LabNumberAllocator::class);
        $manifestAllocator = app(ManifestNumberAllocator::class);
        $yearMonth = now('Asia/Karachi')->format('Ym');
        $businessDate = now('Asia/Karachi')->format('Ymd');

        foreach ($centers as $center) {
            $labAllocator->ensureSequence($center->id, $yearMonth, 1);
            $manifestAllocator->ensureSequence($center->id, $businessDate, 1);
        }
    }

    /**
     * @param  list<string>  $permissions
     */
    private function upsertScopedUser(
        string $username,
        string $name,
        string $email,
        int $organizationId,
        string $userScope,
        ?int $collectionCenterId,
        array $permissions,
    ): User {
        $user = User::withTrashed()->updateOrCreate(
            ['username' => $username],
            [
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(self::DEMO_PASSWORD),
                'branch' => $userScope === User::SCOPE_MAIN_LAB ? 'Main Lab' : 'Collection Center',
                'organization_id' => $organizationId,
                'user_scope' => $userScope,
                'collection_center_id' => $collectionCenterId,
                'deleted_at' => null,
            ]
        );

        $ids = Permission::query()
            ->whereIn('name', $permissions)
            ->pluck('id')
            ->all();

        $user->permissions()->sync($ids);

        return $user;
    }

    /**
     * Match on org+doctor+category+cc+basis (no test_id) to avoid duplicates on re-run.
     *
     * @param  array{basis: string, percent: float|int|null, amount: float|int|null, priority: int}  $attrs
     */
    private function upsertCommissionRule(
        int $organizationId,
        int $doctorId,
        ?int $testCategoryId,
        ?int $collectionCenterId,
        array $attrs,
    ): LimsCommissionRule {
        $query = LimsCommissionRule::query()
            ->where('organization_id', $organizationId)
            ->where('doctor_id', $doctorId)
            ->where('basis', $attrs['basis']);

        if ($testCategoryId === null) {
            $query->whereNull('test_category_id');
        } else {
            $query->where('test_category_id', $testCategoryId);
        }

        if ($collectionCenterId === null) {
            $query->whereNull('collection_center_id');
        } else {
            $query->where('collection_center_id', $collectionCenterId);
        }

        $existing = $query->whereNull('test_id')->first();

        $payload = [
            'organization_id' => $organizationId,
            'doctor_id' => $doctorId,
            'test_category_id' => $testCategoryId,
            'test_id' => null,
            'collection_center_id' => $collectionCenterId,
            'basis' => $attrs['basis'],
            'amount' => $attrs['amount'],
            'percent' => $attrs['percent'],
            'priority' => $attrs['priority'],
            'effective_from' => now('Asia/Karachi')->toDateString(),
            'effective_to' => null,
            'is_active' => true,
        ];

        if ($existing !== null) {
            $existing->fill($payload);
            $existing->save();

            return $existing;
        }

        return LimsCommissionRule::query()->create($payload);
    }

    /**
     * LIMS-native booking + collected sample (no laboratory_patients / vial FKs).
     * Safe for Sample Batch add-item testing.
     */
    private function seedDemoCollectedSample(
        Organization $org,
        CollectionCenter $center,
        LimsDoctor $doctor,
        LimsTestCategory $category,
        User $actor,
        string $syncId,
        string $barcode,
        string $patientName,
        ?Test $demoTest,
    ): void {
        $existingBooking = LimsBooking::withoutGlobalScopes()
            ->where('sync_id', $syncId)
            ->first();

        if ($existingBooking !== null) {
            $sample = LimsSample::withoutGlobalScopes()
                ->where('organization_id', $org->id)
                ->where('barcode', $barcode)
                ->first();

            if ($sample === null) {
                $this->createCollectedSample($org, $center, $existingBooking, $barcode, $actor);
            }

            return;
        }

        $mrNo = 'SEED-'.strtoupper($center->code).'-'.substr(str_replace('-', '', $syncId), 0, 6);

        $patient = LimsPatient::query()->firstOrCreate(
            [
                'organization_id' => $org->id,
                'mr_no' => $mrNo,
            ],
            [
                'full_name' => $patientName,
                'gender' => 'Male',
                'age_years' => 35,
                'contact_no' => '0300999'.substr((string) $center->id, -4),
                'created_at_cc_id' => $center->id,
                'created_by' => $actor->id,
            ]
        );

        $allocated = app(LabNumberAllocator::class)->allocate($center->id);

        $booking = LimsBooking::withoutGlobalScopes()->create([
            'organization_id' => $org->id,
            'collection_center_id' => $center->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'refer_by_doctor_name' => $doctor->name,
            'self_referred' => false,
            'lab_number' => $allocated['lab_number'],
            'lab_number_year_month' => $allocated['year_month'],
            'lab_number_seq' => $allocated['seq'],
            'status' => LimsBooking::STATUS_COLLECTED,
            'booked_at' => now(),
            'booked_by' => $actor->id,
            'sync_id' => $syncId,
            'laboratory_patient_id' => null,
            'notes' => 'LimsTestingSeeder demo booking — ready for sample batch',
        ]);

        if ($demoTest !== null) {
            $item = LimsBookingItem::withoutGlobalScopes()->firstOrCreate(
                [
                    'booking_id' => $booking->id,
                    'test_id' => $demoTest->id,
                ],
                [
                    'collection_center_id' => $center->id,
                    'test_category_id' => $category->id,
                    'test_name_snapshot' => $demoTest->name ?? 'Demo Test',
                    'list_price' => (float) ($demoTest->price ?? 500),
                    'net_price' => (float) ($demoTest->price ?? 500),
                    'discount_amount' => 0,
                    'status' => 'pending',
                    'sample_status' => LimsBookingItem::SAMPLE_COLLECTED,
                ]
            );

            if ($item->test_category_id === null) {
                $item->test_category_id = $category->id;
                $item->save();
            }

            try {
                app(CommissionSnapshotService::class)->snapshotBooking($booking, $actor);
            } catch (\Throwable $e) {
                $this->command?->warn('Commission snapshot skipped for '.$syncId.': '.$e->getMessage());
            }
        }

        $this->createCollectedSample($org, $center, $booking, $barcode, $actor);
    }

    private function createCollectedSample(
        Organization $org,
        CollectionCenter $center,
        LimsBooking $booking,
        string $barcode,
        User $actor,
    ): LimsSample {
        $name = trim((string) ($actor->name ?: $actor->username));

        return LimsSample::withoutGlobalScopes()->updateOrCreate(
            [
                'organization_id' => $org->id,
                'barcode' => $barcode,
            ],
            [
                'collection_center_id' => $center->id,
                'booking_id' => $booking->id,
                'vial_type' => 'EDTA',
                'vial_number' => 1,
                'status' => LimsSample::STATUS_COLLECTED,
                'collected_at' => now(),
                'collected_by' => $actor->id,
                'collected_by_name' => $name !== '' ? $name : 'User #'.$actor->id,
                'lab_sample_vial_id' => null,
            ]
        );
    }
}
