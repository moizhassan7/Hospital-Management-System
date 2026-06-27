<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PathologyTestComment extends Model
{
    protected $fillable = [
        'laboratory_patient_id',
        'test_id',
        'comment',
    ];

    public function laboratoryPatient(): BelongsTo
    {
        return $this->belongsTo(LaboratoryPatient::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }
}
