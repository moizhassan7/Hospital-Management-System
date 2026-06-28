<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'desktop_test_id',
        'name',
        'price',
        'type',
        'report_format',
        'template',
        'test_head_id',
        'priority',
        'report_time',
        'category',
        'is_active',
        'source_hash',
        'source_updated_at',
        'sample_expiry_hours',
        'sample_vial',
        'vials_required',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'source_updated_at' => 'datetime',
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