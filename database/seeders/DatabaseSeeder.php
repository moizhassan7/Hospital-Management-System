<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin Role
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);

        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'branch' => 'Main',
            ]
        );

        // Assign Role if not already assigned
        if (!$admin->roles()->where('name', 'Admin')->exists()) {
            $admin->roles()->attach($adminRole);
        }

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            RolesAndPermissionsSeeder::class,
            PatientSeeder::class,
            DesktopTestCatalogSeeder::class,
            TestParticularsFromExcelSeeder::class,
            PathologyPanelSeeder::class,
        ]);
    }
}
