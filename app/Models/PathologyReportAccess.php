<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PathologyReportAccess extends Model
{
    protected $table = 'pathology_report_access';

    protected $fillable = [
        'laboratory_patient_id',
        'test_id',
        'access_token',
    ];

    public function laboratoryPatient(): BelongsTo
    {
        return $this->belongsTo(LaboratoryPatient::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public static function ensureToken(int $labPatientId, int $testId): self
    {
        return static::firstOrCreate(
            [
                'laboratory_patient_id' => $labPatientId,
                'test_id' => $testId,
            ],
            [
                'access_token' => Str::random(48),
            ]
        );
    }
}
