<?php

namespace App\Models;

use App\Models\Scopes\BelongsToCollectionCenterScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LimsSampleBatch extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_OPEN = 'open';
    public const STATUS_DISPATCHED = 'dispatched';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_CANCELLED = 'cancelled';

    /** Statuses where a sample may still be "in" this batch (active membership). */
    public const ACTIVE_STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_DISPATCHED,
        self::STATUS_IN_TRANSIT,
    ];

    protected $table = 'lims_sample_batches';

    protected $fillable = [
        'organization_id',
        'collection_center_id',
        'destination_site_id',
        'manifest_no',
        'status',
        'sample_count',
        'dispatched_at',
        'dispatched_by',
        'dispatched_by_name',
        'courier_name',
        'courier_ref',
        'in_transit_at',
        'received_at',
        'received_by',
        'received_by_name',
        'idempotency_key',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sample_count' => 'integer',
            'dispatched_at' => 'datetime',
            'in_transit_at' => 'datetime',
            'received_at' => 'datetime',
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

    public function destinationSite(): BelongsTo
    {
        return $this->belongsTo(CollectionCenter::class, 'destination_site_id');
    }

    public function dispatchedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(LimsSampleBatchItem::class, 'batch_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(LimsTransitEvent::class, 'batch_id');
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_OPEN => 'Open',
            self::STATUS_DISPATCHED => 'Dispatched',
            self::STATUS_IN_TRANSIT => 'In transit',
            self::STATUS_RECEIVED => 'Received',
            self::STATUS_CLOSED => 'Closed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            self::STATUS_OPEN => 'hms-badge-blue',
            self::STATUS_DISPATCHED => 'hms-badge-yellow',
            self::STATUS_IN_TRANSIT => 'hms-badge-purple',
            self::STATUS_RECEIVED => 'hms-badge-green',
            self::STATUS_CLOSED => 'hms-badge-gray',
            self::STATUS_CANCELLED => 'hms-badge-red',
            default => 'hms-badge-gray',
        };
    }
}
