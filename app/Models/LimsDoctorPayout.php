<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LimsDoctorPayout extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'lims_doctor_payouts';

    protected $fillable = [
        'organization_id',
        'doctor_id',
        'amount',
        'paid_at',
        'paid_by',
        'method',
        'notes',
        'idempotency_key',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if ($model->created_at === null) {
                $model->created_at = now();
            }
            if ($model->paid_at === null) {
                $model->paid_at = now();
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(LimsDoctor::class, 'doctor_id');
    }

    public function paidByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function ledgerEntry(): HasOne
    {
        return $this->hasOne(LimsLedgerEntry::class, 'payout_id');
    }
}
