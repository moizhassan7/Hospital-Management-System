<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndoorPatientAdvance extends Model
{
    protected $fillable = [
        'indoor_patient_id',
        'amount',
        'date',
        'receipt_no',
        'remarks',
        'created_by'
    ];

    public function indoorPatient()
    {
        return $this->belongsTo(IndoorPatient::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
