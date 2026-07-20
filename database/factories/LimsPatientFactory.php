<?php

namespace Database\Factories;

use App\Models\LimsPatient;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LimsPatient>
 */
class LimsPatientFactory extends Factory
{
    protected $model = LimsPatient::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'mr_no' => (string) fake()->unique()->numberBetween(1, 999999),
            'full_name' => fake()->name(),
            'gender' => fake()->randomElement(['Male', 'Female', 'Other']),
            'date_of_birth' => fake()->optional()->date(),
            'age_years' => fake()->optional()->numberBetween(1, 100),
            'contact_no' => fake()->optional()->numerify('03#########'),
            'cnic' => fake()->optional()->numerify('#####-#######-#'),
            'address' => fake()->optional()->address(),
        ];
    }
}
