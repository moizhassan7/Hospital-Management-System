<?php

namespace App\Models;

use App\Models\Scopes\BelongsToCollectionCenterScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class LimsBookingItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const SAMPLE_BOOKED = 'booked';
    public const SAMPLE_COLLECTED = 'collected';
    public const SAMPLE_DISPATCHED = 'dispatched';
    public const SAMPLE_IN_TRANSIT = 'in_transit';
    public const SAMPLE_RECEIVED = 'received';
    public const SAMPLE_REJECTED = 'rejected';
    public const SAMPLE_EXPIRED = 'expired';
    public const SAMPLE_PROCESSING = 'processing';
    public const SAMPLE_COMPLETED = 'completed';

    protected $table = 'lims_booking_items';

    protected $fillable = [
        'booking_id',
        'collection_center_id',
        'test_id',
        'test_category_id',
        'test_name_snapshot',
        'list_price',
        'net_price',
        'discount_amount',
        'status',
        'sample_status',
    ];

    protected function casts(): array
    {
        return [
            'list_price' => 'decimal:2',
            'net_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new BelongsToCollectionCenterScope);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(LimsBooking::class, 'booking_id');
    }

    public function collectionCenter(): BelongsTo
    {
        return $this->belongsTo(CollectionCenter::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function testCategory(): BelongsTo
    {
        return $this->belongsTo(LimsTestCategory::class, 'test_category_id');
    }

    public function commissionSnapshot(): HasOne
    {
        return $this->hasOne(LimsCommissionSnapshot::class, 'booking_item_id');
    }

    /**
     * Map legacy LabSampleVial / selected_tests sample_status to LIMS sample_status.
     */
    public static function mapLegacySampleStatus(?string $legacy): string
    {
        return match ($legacy) {
            LabSampleVial::STATUS_COLLECTED => self::SAMPLE_COLLECTED,
            LabSampleVial::STATUS_IN_LAB => self::SAMPLE_RECEIVED,
            LabSampleVial::STATUS_PROCESSING => self::SAMPLE_PROCESSING,
            LabSampleVial::STATUS_COMPLETED => self::SAMPLE_COMPLETED,
            LabSampleVial::STATUS_REJECTED => self::SAMPLE_REJECTED,
            LabSampleVial::STATUS_EXPIRED => self::SAMPLE_EXPIRED,
            default => self::SAMPLE_BOOKED,
        };
    }
}
