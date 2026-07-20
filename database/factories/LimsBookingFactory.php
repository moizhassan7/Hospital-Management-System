<?php

namespace Database\Factories;

use App\Models\CollectionCenter;
use App\Models\LimsBooking;
use App\Models\LimsPatient;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LimsBooking>
 */
class LimsBookingFactory extends Factory
{
    protected $model = LimsBooking::class;

    public function definition(): array
    {
        $yearMonth = now('Asia/Karachi')->format('Ym');
        $seq = fake()->unique()->numberBetween(1, 9999);
        $prefix = 'CC1';

        return [
            'organization_id' => Organization::factory(),
            'collection_center_id' => CollectionCenter::factory(),
            'patient_id' => LimsPatient::factory(),
            'self_referred' => true,
            'lab_number' => sprintf('%s-%s-%04d', $prefix, $yearMonth, $seq),
            'lab_number_year_month' => $yearMonth,
            'lab_number_seq' => $seq,
            'status' => LimsBooking::STATUS_BOOKED,
            'booked_at' => now(),
            'sync_id' => (string) Str::uuid(),
        ];
    }
}
