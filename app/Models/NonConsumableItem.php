<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NonConsumableItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'asset_tag',
        'unit',
        'purchase_price',
        'category_id',
        'company_name',
        'purchase_date',
        'warranty_expiry_date',
        'depreciation_rate',
        'current_status',
        'location_id',
        'barcode',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'float',
        'depreciation_rate' => 'float',
        'purchase_date' => 'date',
        'warranty_expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}