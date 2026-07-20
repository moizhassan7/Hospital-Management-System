<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LimsTransitEvent extends Model
{
    public $timestamps = false;

    public const TYPE_CREATED = 'created';
    public const TYPE_DISPATCHED = 'dispatched';
    public const TYPE_SCANNED_IN_TRANSIT = 'scanned_in_transit';
    public const TYPE_RECEIVED = 'received';
    public const TYPE_REJECTED_ITEM = 'rejected_item';
    public const TYPE_REOPENED = 'reopened';
    public const TYPE_CLOSED = 'closed';

    protected $table = 'lims_transit_events';

    protected $fillable = [
        'batch_id',
        'collection_center_id',
        'event_type',
        'occurred_at',
        'actor_user_id',
        'location_label',
        'payload',
        'idempotency_key',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'created_at' => 'datetime',
            'payload' => 'array',
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
            if ($model->payload === null) {
                $model->payload = [];
            }
        });
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(LimsSampleBatch::class, 'batch_id');
    }

    public function collectionCenter(): BelongsTo
    {
        return $this->belongsTo(CollectionCenter::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
