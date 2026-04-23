<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'mr_number',
        'patient_type',
        'registration_date',
        'name',
        'marital_status',
        'date_of_birth',
        'relation_type',
        'guardian_name',
        'guardian_cnic',
        'age',
        'is_welfare',
        'weight',
        'gender',
        'email',
        'cnic',
        'address',
        'mobile_number',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'date_of_birth' => 'date',
        'weight' => 'float',
        'age' => 'integer',
        'is_welfare' => 'boolean', 
    ];
}