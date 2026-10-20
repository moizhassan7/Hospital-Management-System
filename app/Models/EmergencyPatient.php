<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyPatient extends Model
{
    use HasFactory;

    protected $fillable = [
        'mr_number',
        'patient_name',
        'age',
        'gender',
        'emergency_hours',
        'first_hour_charges',
        'other_hours_charges',
        'total_fee',
        'amount_paid',
        'amount_receivable',
        'consultants',
        'received_by',
    ];

    protected $casts = [
        'consultants' => 'array',
    ];

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}