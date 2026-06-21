<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabSampleVial extends Model
{
    use HasFactory;

    protected $fillable = [
        'laboratory_patient_id',
        'barcode',
        'vial_type',
        'vial_number',
        'test_ids',
        'collected_at',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'test_ids' => 'array',
        'collected_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function laboratoryPatient()
    {
        return $this->belongsTo(LaboratoryPatient::class);
    }

    public function tests()
    {
        return Test::whereIn('id', $this->test_ids ?? [])->get();
    }
}
