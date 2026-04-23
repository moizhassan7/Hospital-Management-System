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
        'unit',
        'normal_range_min',
        'normal_range_max',
        'reference_text',
    ];

    /**
     * Get the test that owns the particular.
     */
    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}