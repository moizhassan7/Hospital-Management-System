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
        'vial_volume',
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

    public static function generateNextTestId(string $category = 'Pathology'): string
    {
        $prefix = 'PATH-';

        $max = static::query()
            ->where('test_id', 'like', $prefix . '%')
            ->pluck('test_id')
            ->map(fn (string $id) => preg_match('/^PATH-(\d+)$/', $id, $matches) ? (int) $matches[1] : 0)
            ->max() ?? 0;

        do {
            $max++;
            $candidate = $prefix . str_pad((string) $max, 4, '0', STR_PAD_LEFT);
        } while (static::where('test_id', $candidate)->exists());

        return $candidate;
    }
}