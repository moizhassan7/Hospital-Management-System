<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockIssueItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_issue_id',
        'item_id',
        'item_name',
        'item_unit',
        'item_price',
        'issued_quantity',
        'total_price',
    ];

    protected $casts = [
        'item_price' => 'float',
        'issued_quantity' => 'integer',
        'total_price' => 'float',
    ];

    /**
     * Get the stock issue that owns the item.
     */
    public function stockIssue()
    {
        return $this->belongsTo(StockIssue::class);
    }
}