<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'laboratory_patient_id',
        'test_id',
        'test_particular_id',
        'result_value',
    ];

    public function laboratoryPatient()
    {
        return $this->belongsTo(LaboratoryPatient::class);
    }

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function testParticular()
    {
        return $this->belongsTo(TestParticular::class);
    }
}