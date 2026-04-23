<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaboratoryPatient extends Model
{
    use HasFactory;

    protected $fillable = [
        'mr_no',
        'patient_name',
        'gender',
        'contact_no',
        'age',
        'file_no',
        'priority',
        'self_referred',
        'refer_by_doctor_name',
        'selected_tests',
        'sub_total',
        'discount',
        'grand_total',
        'paid_amount',
        'due_amount',
        'previous_due',
        'status', // Add this if you followed the previous instructions
    ];

    protected $casts = [
        'selected_tests' => 'array',
        'self_referred' => 'boolean',
    ];

    /**
     * Get the tests associated with the patient from the selected_tests array.
     */
    public function tests()
    {
        // Check if selected_tests is not null or empty
        if (!$this->selected_tests) {
            return collect();
        }

        // Get the test IDs from the selected_tests array
        $testIds = collect($this->selected_tests)->pluck('id');

        // Return a collection of Test models
        return Test::whereIn('id', $testIds)->get();
    }
}