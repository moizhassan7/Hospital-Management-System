<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        // app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = [
            'Dashboard' => ['View Dashboard'],
            'Departments' => ['View Departments', 'Manage Departments'],
            'Patients' => ['View Patients', 'Manage Patients'],
            'Doctors' => ['View Doctors', 'Manage Doctors'],
            'Laboratory' => ['View Laboratory', 'Manage Laboratory'],
            'Emergency' => ['View Emergency', 'Manage Emergency'],
            'Store' => ['View Store', 'Manage Store'],
            'Procedures' => ['View Procedures', 'Manage Procedures'],
            'Reports' => ['View Reports'],
            'Finance' => [
                'View Finance',
                'Create Vouchers',
                'Void Vouchers',
                'Manage Voucher Categories',
                'Manage Doctor Payments',
                'Create Cash Handover',
                'Receive Cash Handover',
            ],
            'User Management' => ['View Users', 'Manage Users', 'Manage Roles'],
        ];

        foreach ($modules as $group => $permissions) {
            foreach ($permissions as $permissionName) {
                Permission::firstOrCreate([
                    'name' => $permissionName,
                    'group_name' => $group,
                ]);
            }
        }

        // Create Super Admin Role
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $allPermissions = Permission::all();
        $superAdminRole->permissions()->sync($allPermissions->pluck('id'));

        // Ensure the admin user is Super Admin
        $admin = User::where('username', 'admin')->first();
        if ($admin) {
            $admin->roles()->syncWithoutDetaching([$superAdminRole->id]);
        } else {
            $admin = User::create([
                'name' => 'Super Admin',
                'username' => 'admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('admin123'),
                'branch' => 'Main Branch',
            ]);
            $admin->roles()->attach($superAdminRole);
        }

        // Receptionist — collections + own cash handover / own ledger only
        // (NO View Finance, NO Manage Doctor Payments, NO hospital revenue)
        $receptionistRole = Role::firstOrCreate(['name' => 'Receptionist']);
        $receptionistPerms = Permission::whereIn('name', [
            'View Dashboard',
            'View Patients',
            'Manage Patients',
            'View Emergency',
            'Manage Emergency',
            'View Procedures',
            'Manage Procedures',
            'View Laboratory',
            'Create Cash Handover',
        ])->pluck('id');
        $receptionistRole->permissions()->sync($receptionistPerms);

        // Accountant role - general vouchers + receive handovers + ledger
        $accountantRole = Role::firstOrCreate(['name' => 'Accountant']);
        $accountantPerms = Permission::whereIn('name', [
            'View Dashboard',
            'View Finance',
            'Create Vouchers',
            'Void Vouchers',
            'Manage Doctor Payments',
            'Receive Cash Handover',
            'View Reports',
        ])->pluck('id');
        $accountantRole->permissions()->sync($accountantPerms);

        // Receptionist login
        $receptionist = User::updateOrCreate(
            ['email' => 'receptionist@hospital.com'],
            [
                'name' => 'Receptionist',
                'username' => 'receptionist',
                'password' => Hash::make('reception123'),
                'branch' => 'Main Branch',
            ]
        );
        $receptionist->roles()->sync([$receptionistRole->id]);

        // Accountant login
        $accountant = User::updateOrCreate(
            ['email' => 'accountant@hospital.com'],
            [
                'name' => 'Accountant',
                'username' => 'accountant',
                'password' => Hash::make('account123'),
                'branch' => 'Main Branch',
            ]
        );
        $accountant->roles()->sync([$accountantRole->id]);
    }
}
