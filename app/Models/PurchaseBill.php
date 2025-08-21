<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'purchase_date',
        'bill_id',
        'bill_date',
        'supplier_name',
        'sub_total',
        'total_tax',
        'total_bill_discount',
        'grand_total',
        'received_payment',
        'balance',
        'remarks',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'bill_date' => 'date',
        'sub_total' => 'float',
        'total_tax' => 'float',
        'total_bill_discount' => 'float',
        'grand_total' => 'float',
        'received_payment' => 'float',
        'balance' => 'float',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseBillItem::class);
    }
}