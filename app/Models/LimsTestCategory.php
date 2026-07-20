<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LimsTestCategory extends Model
{
    use HasFactory;

    protected $table = 'lims_test_categories';

    protected $fillable = [
        'organization_id',
        'code',
        'name',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function commissionRules(): HasMany
    {
        return $this->hasMany(LimsCommissionRule::class, 'test_category_id');
    }
}
