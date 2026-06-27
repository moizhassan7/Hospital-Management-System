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
        'report_format',
        'template',
        'test_head_id',
        'priority',
        'report_time',
        'category',
        'sample_expiry_hours',
        'sample_vial',
        'vials_required',
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
        return $this->hasMany(TestParticular::class)->orderBy('sort_order')->orderBy('id');
    }
}