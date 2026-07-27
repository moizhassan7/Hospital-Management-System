<?php

namespace Database\Seeders;

use App\Models\CollectionCenter;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\LabPermissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CollectionCentersAndUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orgId = Organization::firstOrCreate(
            ['name' => 'Default Organization'],
            ['code' => 'ORG1']
        )->id;

        $data = [
            [
                'center_name' => 'Bhalwal',
                'kind' => CollectionCenter::KIND_COLLECTION_CENTER,
                'user_name' => 'Yasir Nazeer',
                'phone' => '0304-1563463',
            ],
            [
                'center_name' => 'Istaklalabad',
                'kind' => CollectionCenter::KIND_COLLECTION_CENTER,
                'user_name' => 'Imran Haider',
                'phone' => '0315-6907643',
            ],
            [
                'center_name' => 'Silanwali',
                'kind' => CollectionCenter::KIND_COLLECTION_CENTER,
                'user_name' => 'Zeeshan Akhtar',
                'phone' => '0346-6572619',
            ],
            [
                'center_name' => 'Shahpur',
                'kind' => CollectionCenter::KIND_COLLECTION_CENTER,
                'user_name' => 'Zeeshan',
                'phone' => '0300-6059164',
            ],
            [
                'center_name' => 'Jauharabad',
                'kind' => CollectionCenter::KIND_COLLECTION_CENTER,
                'user_name' => 'Azhar',
                'phone' => '0315-7044012',
            ],
            [
                'center_name' => 'Noorpur Thal',
                'kind' => CollectionCenter::KIND_COLLECTION_CENTER,
                'user_name' => 'Arslan Tayyab',
                'phone' => '0303-7337436',
            ],
            [
                'center_name' => 'Main Lab - Opposite Shadab Traders',
                'kind' => CollectionCenter::KIND_MAIN_LAB,
                'user_name' => 'Naeem',
                'phone' => '0301-4872894',
            ],
            [
                'center_name' => 'Main Lab - Pharmacy',
                'kind' => CollectionCenter::KIND_MAIN_LAB,
                'user_name' => 'Shehzad',
                'phone' => '0300-8700819',
            ],
            [
                'center_name' => 'Malik lab 12 block',
                'kind' => CollectionCenter::KIND_COLLECTION_CENTER,
                'user_name' => 'Arslan',
                'phone' => '0318-7447803',
            ],
            [
                'center_name' => 'Malik lab AMC',
                'kind' => CollectionCenter::KIND_COLLECTION_CENTER,
                'user_name' => 'Khurram',
                'phone' => '0345-8651360',
            ],
        ];

        // 1. Create Roles & Permissions
        $ccRole = Role::firstOrCreate(['name' => 'Collection Center User']);
        $mlRole = Role::firstOrCreate(['name' => 'Main Lab User']);

        $ccPermissions = Permission::whereIn('name', [
            LabPermissions::BOOKING,
            LabPermissions::SAMPLE_COLLECTION,
            LabPermissions::RESULT_ENTRY,
            LabPermissions::RESULT_EDIT,
            LabPermissions::FRONT_DESK_PRINT,
            LabPermissions::LAB_ATTENDANT,
            LabPermissions::SAMPLES_REPORT,
            LabPermissions::FINANCIAL_SUMMARY,
            LabPermissions::MANAGE_TESTS,
            LabPermissions::MANAGE_DOCTORS,
        ])->pluck('id')->toArray();

        $mlPermissions = Permission::whereNotIn('name', [
            LabPermissions::COLLECTION_CENTERS,
            LabPermissions::COMMISSION_ADMIN,
            LabPermissions::DOCTOR_PAYOUT,
        ])->pluck('id')->toArray();

        $ccRole->permissions()->sync($ccPermissions);
        $mlRole->permissions()->sync($mlPermissions);

        // 2. Create Centers and Users
        foreach ($data as $index => $item) {
            $username = strtolower(str_replace(' ', '', $item['user_name']));

            // Just use the phone number to make usernames unique deterministically
            $phoneSuffix = explode('-', $item['phone'])[1] ?? rand(100, 999);
            if (User::where('username', $username)->exists()) {
                $username .= '.' . $phoneSuffix;
            }

            $centerCode = strtoupper(Str::slug(substr($item['center_name'], 0, 5), '')) . str_pad($index, 2, '0', STR_PAD_LEFT);
            $labPrefix = strtoupper(Str::slug(substr($item['center_name'], 0, 3), '')) . str_pad($index, 2, '0', STR_PAD_LEFT);

            $center = CollectionCenter::firstOrCreate(
                ['name' => $item['center_name'], 'organization_id' => $orgId],
                [
                    'code' => $centerCode,
                    'kind' => $item['kind'],
                    'lab_number_prefix' => $labPrefix,
                    'phone' => $item['phone'],
                    'is_active' => true,
                ]
            );

            $user = User::firstOrCreate(
                ['username' => $username],
                [
                    'name' => $item['user_name'],
                    'email' => $username . '@example.com',
                    'password' => Hash::make('password'),
                    'organization_id' => $orgId,
                    'collection_center_id' => $center->id,
                    'user_scope' => User::SCOPE_COLLECTION_CENTER,
                ]
            );

            // Assign role
            if ($item['kind'] === CollectionCenter::KIND_MAIN_LAB) {
                if (!$user->roles()->where('name', 'Main Lab User')->exists()) {
                    $user->roles()->attach($mlRole->id);
                }
            } else {
                if (!$user->roles()->where('name', 'Collection Center User')->exists()) {
                    $user->roles()->attach($ccRole->id);
                }
            }
        }

        // 3. Create Super Admin (if needed, though admin might already exist)
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin_super@example.com', // changed from admin@admin.com to avoid conflict with DatabaseSeeder
                'password' => Hash::make('admin123'),
                'organization_id' => $orgId,
                'user_scope' => User::SCOPE_MAIN_LAB,
            ]
        );

        if (!$admin->roles()->where('name', 'Super Admin')->exists()) {
            $admin->roles()->attach($superAdminRole->id);
        }
    }
}
