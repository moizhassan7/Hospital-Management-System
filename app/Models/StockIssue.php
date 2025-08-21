<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'issue_date',
        'issue_time',
        'employee_id',
        'employee_name',
        'employee_department',
        'employee_designation',
        'total_issued_quantity',
        'remarks',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'total_issued_quantity' => 'integer',
    ];

    /**
     * Get the items for the stock issue.
     */
    public function items()
    {
        return $this->hasMany(StockIssueItem::class);
    }
}