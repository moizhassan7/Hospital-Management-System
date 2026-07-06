<?php

namespace Database\Seeders;

use App\Models\Test;
use App\Models\TestHead;
use App\Models\TestParticular;
use App\Models\TestResult;
use App\Services\DesktopTestCatalogSyncService;
use App\Support\DesktopSyncHash;
use Database\Seeders\Support\LabParticularResolver;
use Database\Seeders\Support\LabParticularStandards;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Single source of truth for pathology catalog:
 * - Tests from database/seeders/data/lab_tests.json (built from New Test list updated.xlsx)
 * - Vials via PathologyVialMapper (CLSI tube standards)
 * - Particulars via LabParticularResolver (curated JSON + CLSI defaults)
 *
 * Rebuild JSON after Excel changes:
 *   python scripts/build_lab_tests_json.py
 */
class LabCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__ . '/data/lab_tests.json';

        if (! file_exists($jsonPath)) {
            $this->command?->error("Missing {$jsonPath}. Run: python scripts/build_lab_tests_json.py");

            return;
        }

        $rows = json_decode(file_get_contents($jsonPath), true);

        if (! is_array($rows) || $rows === []) {
            $this->command?->error('lab_tests.json is empty or invalid.');

            return;
        }

        $sync = app(DesktopTestCatalogSyncService::class);
        $headCache = [];
        $seenIds = [];
        $testsCreated = 0;
        $testsUpdated = 0;
        $particularCount = 0;
        $zeroParticulars = 0;

        DB::transaction(function () use ($rows, $sync, &$headCache, &$seenIds, &$testsCreated, &$testsUpdated, &$particularCount, &$zeroParticulars) {
            foreach ($rows as $row) {
                $id = (int) ($row['id'] ?? 0);
                $name = trim((string) ($row['name'] ?? ''));

                if ($id <= 0 || $name === '') {
                    continue;
                }

                $seenIds[] = $id;
                $headName = $this->normalizeHead((string) ($row['head'] ?? 'General Pathology'));

                if (! isset($headCache[$headName])) {
                    $headCache[$headName] = TestHead::firstOrCreate(
                        ['name' => $headName, 'category' => 'Pathology'],
                        ['category' => 'Pathology']
                    )->id;
                }

                $vialInfo = PathologyVialMapper::resolve($name, $headName);
                $type = trim((string) ($row['type'] ?? 'Routine')) ?: 'Routine';

                $attributes = [
                    'test_id' => (string) $id,
                    'desktop_test_id' => $id,
                    'name' => $name,
                    'price' => (float) ($row['price'] ?? 0),
                    'type' => $type,
                    'test_head_id' => $headCache[$headName],
                    'category' => 'Pathology',
                    'priority' => $this->mapPriority($type),
                    'report_time' => $sync->parseReportTime((string) ($row['report'] ?? 'Same Day')),
                    'report_format' => 'Quantitative',
                    'sample_vial' => $vialInfo['sample_vial'],
                    'sample_expiry_hours' => $vialInfo['sample_expiry_hours'],
                    'vials_required' => $vialInfo['vials_required'],
                    'is_active' => true,
                ];

                $hash = DesktopSyncHash::make($attributes);
                $attributes['source_hash'] = $hash;
                $attributes['source_updated_at'] = now();

                $existing = Test::find($id);

                if ($existing) {
                    if ($existing->source_hash !== $hash) {
                        $existing->update($attributes);
                        $testsUpdated++;
                    }
                    $test = $existing;
                } else {
                    $test = new Test($attributes);
                    $test->id = $id;
                    $test->save();
                    $testsCreated++;
                }

                TestResult::where('test_id', $test->id)->delete();
                TestParticular::where('test_id', $test->id)->delete();

                $particulars = LabParticularResolver::resolve($id, $name, $headName);

                if ($particulars === []) {
                    $zeroParticulars++;
                    continue;
                }

                foreach ($particulars as $sortOrder => $particularRow) {
                    $this->createParticular($test->id, $particularRow, $sortOrder + 1);
                    $particularCount++;
                }
            }

            if ($seenIds !== []) {
                Test::query()
                    ->where('category', 'Pathology')
                    ->whereNotIn('id', $seenIds)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }

            if (Schema::getConnection()->getDriverName() === 'pgsql') {
                DB::statement("SELECT setval(pg_get_serial_sequence('tests', 'id'), COALESCE((SELECT MAX(id) FROM tests), 1), true)");
            }
        });

        Test::clearPathologyCache();

        $this->command?->info(sprintf(
            'Lab catalog seeded: %d tests (%d new, %d updated), %d particulars, %d tests without particulars.',
            count($seenIds),
            $testsCreated,
            $testsUpdated,
            $particularCount,
            $zeroParticulars
        ));

        if ($zeroParticulars > 0) {
            $this->command?->warn("{$zeroParticulars} tests still have zero particulars — check LabParticularDefaults.");
        }
    }

    /** @param array<string, mixed> $row */
    private function createParticular(int $testId, array $row, int $sortOrder): void
    {
        $name = trim((string) ($row['name'] ?? ''));
        if ($name === '') {
            return;
        }

        $unit = $this->nullableString($row['unit'] ?? null);
        $reference = $this->nullableString($row['reference'] ?? null);
        [$min, $max] = $this->parseRange($reference);
        $meta = LabParticularStandards::metadataFor($name);

        TestParticular::create([
            'test_id' => $testId,
            'name' => $name,
            'result_key' => $meta['result_key'],
            'unit' => $unit,
            'normal_range_min' => $min,
            'normal_range_max' => $max,
            'critical_range_min' => $meta['critical_range_min'],
            'critical_range_max' => $meta['critical_range_max'],
            'reference_text' => $reference,
            'remarks' => $meta['remarks'],
            'formula' => $row['formula'] ?? $meta['formula'],
            'is_calculated' => (bool) ($row['is_calculated'] ?? false) || $meta['is_calculated'],
            'sort_order' => $sortOrder,
            'is_active' => true,
        ]);
    }

    private function normalizeHead(string $head): string
    {
        $head = trim($head);

        if ($head === '' || strcasecmp($head, 'None') === 0 || strcasecmp($head, 'Carry_out') === 0) {
            return 'General Pathology';
        }

        return $head;
    }

    private function mapPriority(string $type): string
    {
        return match (strtolower($type)) {
            'special', 'special01', 'special02', 'pcr' => 'Urgent',
            default => 'Routine',
        };
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    /** @return array{0: float|null, 1: float|null} */
    private function parseRange(?string $reference): array
    {
        if ($reference === null) {
            return [null, null];
        }

        $normalized = strtolower(trim($reference));

        if (preg_match('/^(negative|nil|normal|clear|yellow|alkaline|off white|not seen|report|compatible|detected)/', $normalized)) {
            return [null, null];
        }

        if (str_contains($normalized, '|') && ! preg_match('/(\d+\.?\d*)\s*-\s*(\d+\.?\d*)/', $reference)) {
            if (preg_match('/<\s*(\d+\.?\d*)/', $normalized, $matches)) {
                return [null, floatval($matches[1])];
            }
            if (preg_match('/>=?\s*(\d+\.?\d*)/', $normalized, $matches)) {
                return [floatval($matches[1]), null];
            }

            return [null, null];
        }

        $compact = preg_replace('/\s+/', '', $reference) ?? '';
        $compact = str_replace('--', '-', $compact);

        if (preg_match('/^<\s*(\d+\.?\d*)/', $compact, $matches)) {
            return [null, floatval($matches[1])];
        }

        if (preg_match('/^(\d+\.?\d*)-(\d+\.?\d*)/', $compact, $matches)) {
            return [floatval($matches[1]), floatval($matches[2])];
        }

        if (preg_match('/^>(\d+\.?\d*)$/', $compact, $matches)) {
            return [floatval($matches[1]), null];
        }

        return [null, null];
    }
}
