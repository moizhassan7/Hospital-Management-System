<?php

namespace App\Models;

use App\Models\Scopes\BelongsToCollectionCenterScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LimsSample extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_BOOKED = 'booked';
    public const STATUS_COLLECTED = 'collected';
    public const STATUS_DISPATCHED = 'dispatched';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';

    protected $table = 'lims_samples';

    protected $fillable = [
        'organization_id',
        'collection_center_id',
        'booking_id',
        'barcode',
        'vial_type',
        'vial_number',
        'status',
        'collected_at',
        'collected_by',
        'received_at',
        'received_by',
        'rejected_at',
        'reject_reason',
        'expires_at',
        'lab_sample_vial_id',
    ];

    protected function casts(): array
    {
        return [
            'vial_number' => 'integer',
            'collected_at' => 'datetime',
            'received_at' => 'datetime',
            'rejected_at' => 'datetime',
            'expires_at' => 'datetime',
            'deleted_at' => 'datetime',
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

    public function collectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function labSampleVial(): BelongsTo
    {
        return $this->belongsTo(LabSampleVial::class, 'lab_sample_vial_id');
    }

    public function batchItems(): HasMany
    {
        return $this->hasMany(LimsSampleBatchItem::class, 'sample_id');
    }

    /**
     * Map legacy LabSampleVial status → LIMS sample_status.
     */
    public static function mapLegacyStatus(?string $legacy): string
    {
        return match ($legacy) {
            LabSampleVial::STATUS_COLLECTED => self::STATUS_COLLECTED,
            LabSampleVial::STATUS_IN_LAB => self::STATUS_RECEIVED,
            LabSampleVial::STATUS_PROCESSING => self::STATUS_PROCESSING,
            LabSampleVial::STATUS_COMPLETED => self::STATUS_COMPLETED,
            LabSampleVial::STATUS_REJECTED => self::STATUS_REJECTED,
            LabSampleVial::STATUS_EXPIRED => self::STATUS_EXPIRED,
            default => self::STATUS_BOOKED,
        };
    }

    /**
     * Map LIMS sample_status → legacy vial status (receive bridge).
     */
    public static function toLegacyStatus(string $limsStatus): ?string
    {
        return match ($limsStatus) {
            self::STATUS_COLLECTED => LabSampleVial::STATUS_COLLECTED,
            self::STATUS_RECEIVED => LabSampleVial::STATUS_IN_LAB,
            self::STATUS_PROCESSING => LabSampleVial::STATUS_PROCESSING,
            self::STATUS_COMPLETED => LabSampleVial::STATUS_COMPLETED,
            self::STATUS_REJECTED => LabSampleVial::STATUS_REJECTED,
            self::STATUS_EXPIRED => LabSampleVial::STATUS_EXPIRED,
            default => null,
        };
    }
}
