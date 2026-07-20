<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MrNumberSequence extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'organization_id';

    public $incrementing = false;

    protected $fillable = [
        'organization_id',
        'next_value',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'next_value' => 'integer',
            'updated_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
