<?php

namespace Database\Factories;

use App\Models\CollectionCenter;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CollectionCenter>
 */
class CollectionCenterFactory extends Factory
{
    protected $model = CollectionCenter::class;

    public function definition(): array
    {
        $code = 'CC'.fake()->unique()->numberBetween(1, 99);

        return [
            'organization_id' => Organization::factory(),
            'code' => $code,
            'name' => 'Collection Center '.$code,
            'kind' => CollectionCenter::KIND_COLLECTION_CENTER,
            'lab_number_prefix' => $code,
            'address' => fake()->optional()->address(),
            'phone' => fake()->optional()->phoneNumber(),
            'is_active' => true,
        ];
    }

    public function mainLab(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'MAIN',
            'name' => 'Main Laboratory',
            'kind' => CollectionCenter::KIND_MAIN_LAB,
            'lab_number_prefix' => 'MAIN',
        ]);
    }
}
