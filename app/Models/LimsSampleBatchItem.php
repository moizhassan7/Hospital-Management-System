<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LimsSampleBatchItem extends Model
{
    public $timestamps = false;

    public const RECEIVE_PENDING = 'pending';
    public const RECEIVE_RECEIVED = 'received';
    public const RECEIVE_MISSING = 'missing';
    public const RECEIVE_REJECTED = 'rejected';

    protected $table = 'lims_sample_batch_items';

    protected $fillable = [
        'batch_id',
        'sample_id',
        'booking_id',
        'added_at',
        'receive_status',
        'receive_note',
    ];

    protected function casts(): array
    {
        return [
            'added_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(LimsSampleBatch::class, 'batch_id');
    }

    public function sample(): BelongsTo
    {
        return $this->belongsTo(LimsSample::class, 'sample_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(LimsBooking::class, 'booking_id');
    }
}
