<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InpatientDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'indoor_patient_id',
        'patient_id',
        'consultant_visits',
        'payment_history',
    ];

    protected $casts = [
        'consultant_visits' => 'array',
        'payment_history' => 'array',
    ];

    public function indoorPatient()
    {
        return $this->belongsTo(IndoorPatient::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
