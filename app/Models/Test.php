<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Test extends Model
{
    use HasFactory;

    private const PATHOLOGY_IDS_CACHE_KEY = 'lab.pathology_test_ids';

    private const PATHOLOGY_IDS_TTL_SECONDS = 900;

    protected $fillable = [
        'test_id',
        'name',
        'price',
        'type',
        'report_format',
        'template',
        'reference_tables',
        'test_head_id',
        'priority',
        'report_time',
        'category',
        'test_category_id',
        'is_active',
        'sample_expiry_hours',
        'sample_vial',
        'vials_required',
        'vial_volume',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'reference_tables' => 'array',
    ];

    /**
     * Normalized client-defined reference tables for this test.
     *
     * @return list<array{title: string, columns: list<string>, rows: list<list<string>>}>
     */
    public function referenceTablesArray(): array
    {
        $tables = [];

        foreach ((array) ($this->reference_tables ?? []) as $table) {
            if (! is_array($table)) {
                continue;
            }

            $columns = array_values(array_filter(
                array_map(fn ($c) => trim((string) $c), $table['columns'] ?? []),
                fn ($c) => $c !== ''
            ));

            if ($columns === []) {
                continue;
            }

            $columnCount = count($columns);
            $rows = [];

            foreach ($table['rows'] ?? [] as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $cells = array_map(fn ($cell) => trim((string) $cell), array_values($row));
                // Pad / trim each row to match the column count.
                $cells = array_slice(array_pad($cells, $columnCount, ''), 0, $columnCount);

                if (implode('', $cells) === '') {
                    continue;
                }

                $rows[] = $cells;
            }

            if ($rows === []) {
                continue;
            }

            $tables[] = [
                'title' => trim((string) ($table['title'] ?? '')),
                'columns' => $columns,
                'rows' => $rows,
            ];
        }

        return $tables;
    }

    public function hasReferenceTables(): bool
    {
        return $this->referenceTablesArray() !== [];
    }

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

    public function scopePathology(Builder $query): Builder
    {
        return $query->where('category', 'Pathology');
    }

    /**
     * Cached list of pathology test primary keys — avoids repeated category scans.
     *
     * @return list<int>
     */
    public static function pathologyIds(): array
    {
        return Cache::remember(self::PATHOLOGY_IDS_CACHE_KEY, self::PATHOLOGY_IDS_TTL_SECONDS, function () {
            return static::query()
                ->where('category', 'Pathology')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        });
    }

    /**
     * Cached id => name map for pathology tests.
     *
     * @return Collection<int, string>
     */
    public static function pathologyNamesById(): Collection
    {
        return Cache::remember('lab.pathology_test_names', self::PATHOLOGY_IDS_TTL_SECONDS, function () {
            return static::query()
                ->where('category', 'Pathology')
                ->pluck('name', 'id');
        });
    }

    public static function clearPathologyCache(): void
    {
        Cache::forget(self::PATHOLOGY_IDS_CACHE_KEY);
        Cache::forget('lab.pathology_test_names');
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