<?php

namespace Database\Seeders;

use App\Models\Test;
use App\Models\TestHead;
use App\Models\TestParticular;
use App\Models\TestResult;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Seeds pathology catalog from lab-tests Excel exports.
 *
 * Rebuild JSON after Excel changes:
 *   python scripts/build_lab_catalog_json.py
 */
class LabTestCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__ . '/data/lab_catalog.json';

        if (! file_exists($jsonPath)) {
            $this->command?->error("Missing {$jsonPath}. Run: python scripts/build_lab_catalog_json.py");

            return;
        }

        $catalog = json_decode(file_get_contents($jsonPath), true);

        if (! is_array($catalog) || ($catalog['tests'] ?? []) === []) {
            $this->command?->error('lab_catalog.json is empty or invalid.');

            return;
        }

        $headCache = [];
        $seenIds = [];
        $testsCreated = 0;
        $testsUpdated = 0;
        $particularCount = 0;
        $zeroParticulars = 0;

        DB::transaction(function () use ($catalog, &$headCache, &$seenIds, &$testsCreated, &$testsUpdated, &$particularCount, &$zeroParticulars) {
            foreach ($catalog['tests'] as $row) {
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
                $testCode = trim((string) ($row['test_code'] ?? ''));

                $attributes = [
                    'external_id' => $id,
                    'test_id' => $testCode !== '' ? $testCode : (string) $id,
                    'test_code' => $testCode !== '' ? $testCode : null,
                    'name' => $name,
                    'price' => (float) ($row['price'] ?? 0),
                    'type' => $type,
                    'test_head_id' => $headCache[$headName],
                    'category' => 'Pathology',
                    'priority' => $this->mapPriority($type),
                    'report_time' => $this->parseReportTime((string) ($row['report'] ?? 'Same Day')),
                    'report_format' => 'Quantitative',
                    'sample_vial' => $vialInfo['sample_vial'],
                    'sample_expiry_hours' => $vialInfo['sample_expiry_hours'],
                    'vials_required' => $vialInfo['vials_required'],
                    'is_active' => true,
                ];

                $existing = Test::find($id);

                if ($existing) {
                    $existing->update($attributes);
                    $testsUpdated++;
                    $test = $existing;
                } else {
                    $test = new Test($attributes);
                    $test->id = $id;
                    $test->save();
                    $testsCreated++;
                }

                TestResult::where('test_id', $test->id)->delete();
                TestParticular::where('test_id', $test->id)->delete();

                $particulars = $row['particulars'] ?? [];

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
    }

    private function parseReportTime(string $report): int
    {
        $report = strtolower(trim($report));

        if ($report === '' || $report === 'same day' || $report === 'routine') {
            return 12;
        }

        if (preg_match('/(\d+)\s*(hour|hr)/', $report, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/(\d+)\s*(day|dy)/', $report, $matches)) {
            return (int) $matches[1] * 24;
        }

        return 24;
    }

    /** @param array<string, mixed> $row */
    private function createParticular(int $testId, array $row, int $sortOrder): void
    {
        $name = trim((string) ($row['name'] ?? ''));
        if ($name === '') {
            return;
        }

        TestParticular::create([
            'external_id' => isset($row['external_id']) ? (int) $row['external_id'] : null,
            'test_id' => $testId,
            'name' => $name,
            'patient_type' => $this->nullableString($row['patient_type'] ?? null),
            'result_key' => $this->nullableString($row['result_key'] ?? null) ?? Str::slug($name, '_'),
            'unit' => $this->nullableString($row['unit'] ?? null),
            'normal_range_min' => $this->nullableString($row['normal_range_min'] ?? null),
            'normal_range_max' => $this->nullableString($row['normal_range_max'] ?? null),
            'critical_range_min' => $this->nullableString($row['critical_range_min'] ?? null),
            'critical_range_max' => $this->nullableString($row['critical_range_max'] ?? null),
            'reference_range_text' => $this->nullableString($row['reference_range_text'] ?? null),
            'reference_text' => null,
            'interpretation_name' => $this->nullableString($row['interpretation_name'] ?? null),
            'formula' => null,
            'is_calculated' => false,
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
}
