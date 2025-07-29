<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        Location::create(['name' => 'Main Store']);
        Location::create(['name' => 'Pharmacy Dept']);
        Location::create(['name' => 'Emergency Ward']);
    }
}