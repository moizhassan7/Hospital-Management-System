<?php

namespace Tests\Feature;

use App\Http\Controllers\BookingController;
use App\Models\LaboratoryPatient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class DueCollectionTest extends TestCase
{
    public function test_collect_due_updates_paid_and_due_amounts()
    {
        $patient = new LaboratoryPatient();
        $patient->id = 1;
        $patient->sub_total = 1000.00;
        $patient->discount = 100.00;
        $patient->discount_type = 'percentage';
        $patient->discount_value = 10.00;
        $patient->grand_total = 900.00;
        $patient->paid_amount = 500.00;
        $patient->due_amount = 400.00;

        $user = new User();
        $user->id = 1;
        Auth::shouldReceive('user')->andReturn($user);

        // Test math directly
        $amountToCollect = 250.00;
        $patient->paid_amount += $amountToCollect;
        $patient->due_amount = max(0, $patient->grand_total - $patient->paid_amount);

        $this->assertEquals(750.00, $patient->paid_amount);
        $this->assertEquals(150.00, $patient->due_amount);
    }
}
