<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LimsDoctorLedger extends Model
{
    use HasFactory;

    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'lims_doctor_ledgers';

    protected $primaryKey = 'doctor_id';

    protected $fillable = [
        'doctor_id',
        'organization_id',
        'balance',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'updated_at' => 'datetime',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(LimsDoctor::class, 'doctor_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
