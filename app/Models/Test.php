<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'name',
        'price',
        'type',
        'test_head_id',
        'priority',
        'report_time',
    ];

    public function testHead()
    {
        return $this->belongsTo(TestHead::class);
    }

    /**
     * Get the particulars for the test.
     */
    public function testParticulars()
    {
        return $this->hasMany(TestParticular::class);
    }
}