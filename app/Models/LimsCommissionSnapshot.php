<?php

namespace App\Models;

use App\Models\Scopes\BelongsToCollectionCenterScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable commission fact at booking time.
 * Only is_clawed_back / clawed_back_at may change after insert.
 */
class LimsCommissionSnapshot extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'lims_commission_snapshots';

    protected $fillable = [
        'organization_id',
        'collection_center_id',
        'booking_id',
        'booking_item_id',
        'invoice_id',
        'doctor_id',
        'commission_rule_id',
        'rule_basis',
        'rule_amount',
        'rule_percent',
        'base_amount',
        'commission_amount',
        'currency',
        'snapshotted_at',
        'snapshotted_by',
        'is_clawed_back',
        'clawed_back_at',
    ];

    protected function casts(): array
    {
        return [
            'rule_amount' => 'decimal:2',
            'rule_percent' => 'decimal:4',
            'base_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'snapshotted_at' => 'datetime',
            'is_clawed_back' => 'boolean',
            'clawed_back_at' => 'datetime',
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

    public function booking(): BelongsTo
    {
        return $this->belongsTo(LimsBooking::class, 'booking_id');
    }

    public function bookingItem(): BelongsTo
    {
        return $this->belongsTo(LimsBookingItem::class, 'booking_item_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(LimsInvoice::class, 'invoice_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(LimsDoctor::class, 'doctor_id');
    }

    public function commissionRule(): BelongsTo
    {
        return $this->belongsTo(LimsCommissionRule::class, 'commission_rule_id');
    }

    public function snapshottedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'snapshotted_by');
    }
}
