<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
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
            'User Management' => ['View Users', 'Manage Users', 'Manage Roles'],
        ];

        foreach ($modules as $group => $permissions) {
            foreach ($permissions as $permissionName) {
                Permission::firstOrCreate([
                    'name' => $permissionName,
                    'group_name' => $group
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
                'branch' => 'Main Branch'
            ]);
            $admin->roles()->attach($superAdminRole);
        }
    }
}
