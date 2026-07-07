<?php

namespace Database\Seeders;

use App\Models\Test;
use App\Models\TestParticular;
use App\Models\TestResult;
use Database\Seeders\Support\LabParticularStandards;
use Illuminate\Database\Seeder;

/**
 * Seeds Cross Match (catalog id 14) report particulars in clinical report order.
 *
 * Data: database/seeders/data/cross_match_particulars.json
 *
 * Run after LabCatalogSeeder:
 *   php artisan db:seed --class=CrossMatchSeeder
 */
class CrossMatchSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__ . '/data/cross_match_particulars.json';

        if (! file_exists($jsonPath)) {
            $this->command?->error("Missing {$jsonPath}");

            return;
        }

        $block = json_decode(file_get_contents($jsonPath), true);

        if (! is_array($block) || ($block['particulars'] ?? []) === []) {
            $this->command?->error('cross_match_particulars.json is empty or invalid.');

            return;
        }

        $test = $this->resolveTest($block);

        if (! $test) {
            $this->command?->error('Cross Match test not found in pathology catalog.');

            return;
        }

        TestResult::where('test_id', $test->id)->delete();
        TestParticular::where('test_id', $test->id)->delete();

        $particularCount = 0;

        foreach ($block['particulars'] as $sortOrder => $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $unit = $this->nullableString($row['unit'] ?? null);
            $reference = $this->nullableString($row['reference'] ?? null);
            [$min, $max] = $this->parseRange($reference);
            $meta = LabParticularStandards::metadataFor($name);

            TestParticular::create([
                'test_id' => $test->id,
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
                'sort_order' => $sortOrder + 1,
                'is_active' => true,
            ]);

            $particularCount++;
        }

        Test::clearPathologyCache();

        $this->command?->newLine();
        $this->command?->info("Cross Match particulars seeded for {$test->name} ({$test->id}): {$particularCount} parameters.");
        $this->command?->table(
            ['#', 'Parameter', 'Unit'],
            collect($block['particulars'])->values()->map(
                fn (array $row, int $index) => [
                    (string) ($index + 1),
                    (string) ($row['name'] ?? ''),
                    (string) ($row['unit'] ?? '—'),
                ]
            )->all()
        );
    }

    /**
     * @param  array{catalog_id?: int, lookup_names?: list<string>, test_name?: string}  $block
     */
    private function resolveTest(array $block): ?Test
    {
        $catalogId = (int) ($block['catalog_id'] ?? 0);

        if ($catalogId > 0) {
            $test = Test::pathology()->find($catalogId);
            if ($test) {
                return $test;
            }
        }

        foreach ($block['lookup_names'] ?? [] as $lookup) {
            $pattern = strtolower(trim((string) $lookup));
            if ($pattern === '') {
                continue;
            }

            $test = Test::pathology()
                ->whereRaw('LOWER(name) = ?', [$pattern])
                ->first();

            if ($test) {
                return $test;
            }
        }

        $testName = trim((string) ($block['test_name'] ?? ''));

        if ($testName !== '') {
            return Test::pathology()
                ->whereRaw('LOWER(name) = ?', [strtolower($testName)])
                ->first();
        }

        return null;
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
