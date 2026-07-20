<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sequence row for safe manifest number allocation (never MAX+1).
 * PK: (collection_center_id, business_date YYYYMMDD Asia/Karachi).
 */
class LimsManifestSequence extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $table = 'lims_manifest_sequences';

    protected $fillable = [
        'collection_center_id',
        'business_date',
        'next_seq',
    ];

    protected function casts(): array
    {
        return [
            'next_seq' => 'integer',
        ];
    }

    public function collectionCenter(): BelongsTo
    {
        return $this->belongsTo(CollectionCenter::class);
    }
}
