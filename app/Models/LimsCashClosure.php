<?php

namespace App\Models;

use App\Models\Scopes\BelongsToCollectionCenterScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LimsCashClosure extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_LOCKED = 'locked';

    protected $table = 'lims_cash_closures';

    protected $fillable = [
        'organization_id',
        'collection_center_id',
        'business_date',
        'shift_label',
        'status',
        'opening_float',
        'system_cash_total',
        'system_card_total',
        'system_other_total',
        'counted_cash_total',
        'variance_cash',
        'due_total',
        'booking_count',
        'payment_count',
        'summary_json',
        'opened_by',
        'submitted_at',
        'submitted_by',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'business_date' => 'date',
            'opening_float' => 'decimal:2',
            'system_cash_total' => 'decimal:2',
            'system_card_total' => 'decimal:2',
            'system_other_total' => 'decimal:2',
            'counted_cash_total' => 'decimal:2',
            'variance_cash' => 'decimal:2',
            'due_total' => 'decimal:2',
            'summary_json' => 'array',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new BelongsToCollectionCenterScope);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function collectionCenter(): BelongsTo
    {
        return $this->belongsTo(CollectionCenter::class);
    }

    public function openedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function submittedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LimsPayment::class, 'cash_closure_id');
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [self::STATUS_LOCKED, self::STATUS_REJECTED], true);
    }
}
