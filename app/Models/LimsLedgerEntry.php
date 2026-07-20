<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LimsLedgerEntry extends Model
{
    use HasFactory;

    public const TYPE_CREDIT = 'credit';
    public const TYPE_DEBIT = 'debit';
    public const TYPE_CLAWBACK = 'clawback';
    public const TYPE_ADJUSTMENT = 'adjustment';

    public const REF_BOOKING = 'booking';
    public const REF_INVOICE = 'invoice';
    public const REF_PAYOUT = 'payout';
    public const REF_CANCELLATION = 'cancellation';
    public const REF_MANUAL = 'manual';

    public $timestamps = false;

    protected $table = 'lims_ledger_entries';

    protected $fillable = [
        'organization_id',
        'doctor_id',
        'collection_center_id',
        'entry_type',
        'amount',
        'balance_after',
        'ref_type',
        'ref_id',
        'commission_snapshot_id',
        'payout_id',
        'narration',
        'occurred_at',
        'created_by',
        'idempotency_key',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'occurred_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if ($model->created_at === null) {
                $model->created_at = now();
            }
            if ($model->occurred_at === null) {
                $model->occurred_at = now();
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

    public function collectionCenter(): BelongsTo
    {
        return $this->belongsTo(CollectionCenter::class);
    }

    public function commissionSnapshot(): BelongsTo
    {
        return $this->belongsTo(LimsCommissionSnapshot::class, 'commission_snapshot_id');
    }

    public function payout(): BelongsTo
    {
        return $this->belongsTo(LimsDoctorPayout::class, 'payout_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
