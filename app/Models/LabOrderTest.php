<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabOrderTest extends Model
{
    protected $fillable = [
        'lab_order_id',
        'desktop_line_id',
        'desktop_test_id',
        'test_id',
        'test_name_snapshot',
        'price_snapshot',
        'status',
        'is_cancelled',
        'source_hash',
    ];

    protected $casts = [
        'is_cancelled' => 'boolean',
        'price_snapshot' => 'float',
    ];

    public function labOrder(): BelongsTo
    {
        return $this->belongsTo(LabOrder::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }
}
