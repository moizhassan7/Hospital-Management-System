<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_id',
        'return_date',
        'employee_id',
        'employee_name',
        'item_id',
        'item_name',
        'item_type',
        'quantity',
        'remarks',
    ];

    protected $casts = [
        'return_date' => 'date',
        'quantity' => 'integer',
    ];
}