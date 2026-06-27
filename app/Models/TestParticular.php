<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestParticular extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'name',
        'result_key',
        'unit',
        'normal_range_min',
        'normal_range_max',
        'critical_range_min',
        'critical_range_max',
        'reference_text',
        'remarks',
        'formula',
        'is_calculated',
        'sort_order',
    ];

    protected $casts = [
        'is_calculated' => 'boolean',
        'normal_range_min' => 'float',
        'normal_range_max' => 'float',
        'critical_range_min' => 'float',
        'critical_range_max' => 'float',
    ];

    /**
     * Get the test that owns the particular.
     */
    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}