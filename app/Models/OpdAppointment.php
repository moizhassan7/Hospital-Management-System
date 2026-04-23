<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpdAppointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_number',
        'appointment_date',
        'appointment_time',
        'mr_number',
        'patient_name',
        'age',
        'gender',
        'doctor_code',
        'doctor_name',
        'doctor_fee',
        'referred_by',
        'total_amount',
        'token_number',
    ];
}