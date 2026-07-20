<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabNumberSequence extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $table = 'lab_number_sequences';

    protected $fillable = [
        'collection_center_id',
        'year_month',
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
