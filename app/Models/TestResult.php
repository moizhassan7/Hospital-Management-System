<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'laboratory_patient_id',
        'test_id',
        'test_particular_id',
        'result_value',
        'entered_by_user_id',
        'sync_id',
        'sync_status',
        'synced_at',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->sync_id)) {
                $model->sync_id = (string) Str::uuid();
            }
            if (empty($model->sync_status)) {
                $model->sync_status = 'pending';
            }
        });

        static::updating(function ($model) {
            if (!$model->isDirty('sync_status')) {
                $model->sync_status = 'pending';
            }
        });
    }

    public function enteredBy()
    {
        return $this->belongsTo(User::class, 'entered_by_user_id');
    }

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