<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseBillItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_bill_id',
        'item_id',
        'item_name',
        'item_unit',
        'quantity',
        'purchase_price',
        'sale_price',
        'tax_percentage',
        'discount_value',
        'discount_type',
        'item_total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'purchase_price' => 'float',
        'sale_price' => 'float',
        'tax_percentage' => 'float',
        'discount_value' => 'float',
        'item_total' => 'float',
    ];

    public function purchaseBill()
    {
        return $this->belongsTo(PurchaseBill::class);
    }
}