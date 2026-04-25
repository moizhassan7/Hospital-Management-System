<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'is_draft',
        'complaints',
        'bp',
        'pulse',
        'temperature',
        'weight',
        'oxygen',
        'diagnoses',
        'reports',
        'medicines',
        'abstains',
        'notes',
        'next_visit_date',
    ];

    protected $casts = [
        'diagnoses' => 'array',
        'reports' => 'array',
        'medicines' => 'array',
        'abstains' => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
