<?php

namespace Database\Seeders;

use App\Models\Test;
use App\Models\TestParticular;
use App\Models\TestResult;
use Database\Seeders\Support\LabParticularStandards;
use Illuminate\Database\Seeder;

/**
 * Seeds pathology test particulars from database/seeders/data/updated_test_particulars.json
 * in the exact document sequence (Test particular.docx / Test particular (1).docx).
 */
class UpdatedTestParticularsSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__ . '/data/updated_test_particulars.json';

        if (! file_exists($jsonPath)) {
            $this->command?->error("Data file not found: {$jsonPath}");

            return;
        }

        $payload = json_decode(file_get_contents($jsonPath), true);

        if (! is_array($payload)) {
            $this->command?->error('Failed to decode updated_test_particulars.json: ' . json_last_error_msg());

            return;
        }

        $seededTests = 0;
        $seededParticulars = 0;
        $missing = [];

        foreach ($payload as $block) {
            $test = $this->resolveTest($block);

            if (! $test) {
                $missing[] = $block['test_name'] ?? ($block['test_key'] ?? 'unknown');
                continue;
            }

            TestResult::where('test_id', $test->id)->delete();
            TestParticular::where('test_id', $test->id)->delete();

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
                    'formula' => $meta['formula'],
                    'is_calculated' => $meta['is_calculated'],
                    'sort_order' => $sortOrder + 1,
                    'is_active' => true,
                ]);

                $seededParticulars++;
            }

            $seededTests++;
            $this->command?->info("Updated particulars for {$test->name} ({$test->id}) in document order.");
        }

        $this->command?->info("Seeded {$seededParticulars} particulars across {$seededTests} tests.");

        if ($missing !== []) {
            $this->command?->warn('Tests not found in catalog (' . count($missing) . '): ' . implode(', ', $missing));
        }
    }

    /**
     * @param  array{test_key?: string, lookup_names?: list<string>, test_name?: string}  $block
     */
    private function resolveTest(array $block): ?Test
    {
        $names = $block['lookup_names'] ?? [];

        foreach ($names as $pattern) {
            $test = Test::where('category', 'Pathology')
                ->whereRaw('LOWER(name) = ?', [strtolower($pattern)])
                ->first();

            if ($test) {
                return $test;
            }

            $test = Test::where('category', 'Pathology')
                ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($pattern) . '%'])
                ->orderByRaw('LENGTH(name)')
                ->first();

            if ($test) {
                return $test;
            }
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

    /**
     * @return array{0: float|null, 1: float|null}
     */
    private function parseRange(?string $reference): array
    {
        if ($reference === null) {
            return [null, null];
        }

        $normalized = strtolower(trim($reference));

        if (in_array($normalized, ['nil', 'normal', 'clear', 'yellow', 'off white', 'alkaline'], true)) {
            return [null, null];
        }

        if (str_contains($normalized, 'desirable') || str_contains($normalized, 'borderline') || str_contains($normalized, '|')) {
            if (preg_match('/<\s*(\d+\.?\d*)/', $normalized, $matches)) {
                return [null, floatval($matches[1])];
            }

            return [null, null];
        }

        if (str_starts_with($normalized, 'more than')) {
            if (preg_match('/(\d+\.?\d*)/', $normalized, $matches)) {
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

        if (preg_match('/^(\d+\.?\d*)$/', $compact, $matches)) {
            return [null, floatval($matches[1])];
        }

        if (preg_match('/^>(\d+\.?\d*)$/', $compact, $matches)) {
            return [floatval($matches[1]), null];
        }

        return [null, null];
    }
}
