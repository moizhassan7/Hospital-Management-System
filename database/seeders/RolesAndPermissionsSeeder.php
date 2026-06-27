<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\LabPermissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $validNames = LabPermissions::all();

        foreach ($validNames as $permissionName) {
            Permission::updateOrCreate(
                ['name' => $permissionName],
                ['group_name' => LabPermissions::GROUP]
            );
        }

        $staleIds = Permission::query()
            ->whereNotIn('name', $validNames)
            ->pluck('id');

        if ($staleIds->isNotEmpty()) {
            DB::table('permission_role')->whereIn('permission_id', $staleIds)->delete();
            DB::table('permission_user')->whereIn('permission_id', $staleIds)->delete();
            Permission::query()->whereIn('id', $staleIds)->delete();
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdminRole->permissions()->sync(Permission::query()->pluck('id'));

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
    }
}
