<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PathologyResultAlert extends Model
{
    public const TYPE_ABNORMAL = 'abnormal';

    public const TYPE_CRITICAL = 'critical';

    protected $fillable = [
        'laboratory_patient_id',
        'test_id',
        'test_particular_id',
        'result_value',
        'alert_type',
        'flag',
        'reported_doctor_name',
        'acknowledged_by',
    ];

    public function laboratoryPatient(): BelongsTo
    {
        return $this->belongsTo(LaboratoryPatient::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function testParticular(): BelongsTo
    {
        return $this->belongsTo(TestParticular::class);
    }

    public function acknowledgedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }
}
