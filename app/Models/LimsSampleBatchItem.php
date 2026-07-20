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
        'receive_marked_by',
        'receive_marked_by_name',
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

    public function receiveMarkedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receive_marked_by');
    }

    public static function receiveStatusLabel(string $status): string
    {
        return match ($status) {
            self::RECEIVE_PENDING => 'Pending',
            self::RECEIVE_RECEIVED => 'Received',
            self::RECEIVE_MISSING => 'Missing',
            self::RECEIVE_REJECTED => 'Rejected',
            default => ucfirst($status),
        };
    }

    public static function receiveStatusBadgeClass(string $status): string
    {
        return match ($status) {
            self::RECEIVE_PENDING => 'hms-badge-gray',
            self::RECEIVE_RECEIVED => 'hms-badge-green',
            self::RECEIVE_MISSING => 'hms-badge-yellow',
            self::RECEIVE_REJECTED => 'hms-badge-red',
            default => 'hms-badge-gray',
        };
    }
}
