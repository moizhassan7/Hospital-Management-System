<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tests = \App\Models\Test::where('category', 'Pathology')->where('is_active', true)->take(5)->get();
        
        if ($tests->isEmpty()) {
            return;
        }

        $fullBody = \App\Models\TestPackage::create([
            'name' => 'Full Body Test',
            'description' => 'Comprehensive full body screening profile.',
            'price' => 15000,
            'is_active' => true,
        ]);
        
        $fullBody->tests()->sync($tests->pluck('id'));

        $medical = \App\Models\TestPackage::create([
            'name' => 'Medical Test',
            'description' => 'Standard medical checkup for visas/jobs.',
            'price' => 5000,
            'is_active' => true,
        ]);

        $medical->tests()->sync($tests->take(2)->pluck('id'));
    }
}
