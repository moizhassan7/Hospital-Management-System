<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::create(['name' => 'Medicines']);
        Category::create(['name' => 'Surgical Supplies']);
        Category::create(['name' => 'Disposables']);
        Category::create(['name' => 'Lab Reagents']);
    }
}