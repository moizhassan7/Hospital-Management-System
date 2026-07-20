<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'timezone',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    public function collectionCenters(): HasMany
    {
        return $this->hasMany(CollectionCenter::class);
    }

    public function mainLab(): HasOne
    {
        return $this->hasOne(CollectionCenter::class)
            ->where('kind', CollectionCenter::KIND_MAIN_LAB);
    }

    public function limsPatients(): HasMany
    {
        return $this->hasMany(LimsPatient::class);
    }

    public function mrNumberSequence(): HasOne
    {
        return $this->hasOne(MrNumberSequence::class);
    }
}
