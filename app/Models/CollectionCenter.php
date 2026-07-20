<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CollectionCenter extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const KIND_MAIN_LAB = 'main_lab';
    public const KIND_COLLECTION_CENTER = 'collection_center';

    protected $fillable = [
        'organization_id',
        'code',
        'name',
        'kind',
        'lab_number_prefix',
        'address',
        'phone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(LimsBooking::class);
    }

    public function samples(): HasMany
    {
        return $this->hasMany(LimsSample::class);
    }

    public function sampleBatches(): HasMany
    {
        return $this->hasMany(LimsSampleBatch::class);
    }

    public function isMainLab(): bool
    {
        return $this->kind === self::KIND_MAIN_LAB;
    }

    public function isCollectionCenter(): bool
    {
        return $this->kind === self::KIND_COLLECTION_CENTER;
    }
}
