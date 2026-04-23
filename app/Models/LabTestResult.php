<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'test_id',
        'case_number',
        'result',
        'status',
    ];

    /**
     * Get the patient that owns the test result.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the test that owns the result.
     */
    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}
