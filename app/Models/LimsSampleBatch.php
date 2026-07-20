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
        'courier_name',
        'courier_ref',
        'in_transit_at',
        'received_at',
        'received_by',
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
}
