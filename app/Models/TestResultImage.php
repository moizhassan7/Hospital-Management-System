<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestResultImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'laboratory_patient_id',
        'test_id',
        'image_path',
    ];

    public function laboratoryPatient()
    {
        return $this->belongsTo(LaboratoryPatient::class);
    }

    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}
