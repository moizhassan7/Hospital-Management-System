<?php

namespace Database\Seeders;

use App\Models\Test;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Applies multi-vial sample collection rules to pathology tests (2+ barcodes per collection).
 *
 * Data: database/seeders/data/multi_vial_tests.json
 *
 * Run after LabCatalogSeeder:
 *   php artisan db:seed --class=MultiVialTestSeeder
 */
class MultiVialTestSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__ . '/data/multi_vial_tests.json';

        if (! file_exists($jsonPath)) {
            $this->command?->error("Missing {$jsonPath}");

            return;
        }

        $rules = json_decode(file_get_contents($jsonPath), true);

        if (! is_array($rules) || $rules === []) {
            $this->command?->error('multi_vial_tests.json is empty or invalid.');

            return;
        }

        $summaryRows = [];
        $updatedTestIds = [];
        $totalTestsUpdated = 0;

        foreach ($rules as $rule) {
            $tests = $this->findTestsForRule($rule);
            $barcodeCount = $this->barcodeCountForRule($rule);

            if ($tests->isEmpty()) {
                $summaryRows[] = [
                    $rule['group'] ?? '—',
                    '—',
                    $this->formatVialConfig($rule),
                    (string) $barcodeCount,
                    'No matching tests in catalog',
                ];

                continue;
            }

            foreach ($tests as $test) {
                if (in_array($test->id, $updatedTestIds, true)) {
                    continue;
                }

                $test->update([
                    'sample_vial' => trim((string) $rule['sample_vial']),
                    'vials_required' => $this->vialsRequiredForRule($rule),
                    'sample_expiry_hours' => (int) ($rule['sample_expiry_hours'] ?? $test->sample_expiry_hours ?? 24),
                ]);

                $updatedTestIds[] = $test->id;
                $totalTestsUpdated++;
            }

            $summaryRows[] = [
                $rule['group'] ?? '—',
                $tests->pluck('name')->implode('; '),
                $this->formatVialConfig($rule),
                (string) $barcodeCount,
                $rule['note'] ?? '',
            ];
        }

        Test::clearPathologyCache();

        $this->command?->newLine();
        $this->command?->info('Multi-vial test rules (2+ barcodes per sample collection):');
        $this->command?->table(
            ['Group', 'Tests matched', 'Vial setup', 'Barcodes', 'Note'],
            $summaryRows
        );
        $this->command?->info("Updated {$totalTestsUpdated} test(s) in catalog.");
    }

    /**
     * @param  array<string, mixed>  $rule
     */
    private function findTestsForRule(array $rule)
    {
        $matched = collect();
        $testIds = array_values(array_filter(array_map('intval', $rule['test_ids'] ?? [])));

        if ($testIds !== []) {
            $matched = Test::pathology()
                ->whereIn('id', $testIds)
                ->orderBy('name')
                ->get();
        }

        $keywords = array_values(array_filter(array_map(
            fn ($keyword) => strtolower(trim((string) $keyword)),
            $rule['keywords'] ?? []
        )));

        if ($keywords === []) {
            return $matched->unique('id')->values();
        }

        $byKeyword = Test::pathology()
            ->orderBy('name')
            ->get()
            ->filter(function (Test $test) use ($keywords) {
                $name = strtolower($test->name);

                foreach ($keywords as $keyword) {
                    if ($keyword === '') {
                        continue;
                    }

                    if (str_starts_with($keyword, '/') && str_ends_with($keyword, '/')) {
                        if (preg_match($keyword, $name)) {
                            return true;
                        }

                        continue;
                    }

                    if (str_contains($name, $keyword)) {
                        return true;
                    }
                }

                return false;
            });

        return $matched
            ->merge($byKeyword)
            ->unique('id')
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    /**
     * @param  array<string, mixed>  $rule
     */
    private function barcodeCountForRule(array $rule): int
    {
        $vialStr = trim((string) ($rule['sample_vial'] ?? 'General'));

        if (str_contains($vialStr, ',')) {
            return count(array_filter(array_map('trim', explode(',', $vialStr))));
        }

        return max(2, (int) ($rule['vials_required'] ?? 2));
    }

    /**
     * @param  array<string, mixed>  $rule
     */
    private function vialsRequiredForRule(array $rule): int
    {
        $vialStr = trim((string) ($rule['sample_vial'] ?? 'General'));

        if (str_contains($vialStr, ',')) {
            return 1;
        }

        return max(2, (int) ($rule['vials_required'] ?? 2));
    }

    /**
     * @param  array<string, mixed>  $rule
     */
    private function formatVialConfig(array $rule): string
    {
        $vialStr = trim((string) ($rule['sample_vial'] ?? 'General'));

        if (str_contains($vialStr, ',')) {
            $types = array_values(array_filter(array_map('trim', explode(',', $vialStr))));

            return implode(' + ', array_map(
                fn (string $type) => Str::limit($type, 18, '…'),
                $types
            ));
        }

        $count = max(2, (int) ($rule['vials_required'] ?? 2));

        return "{$count}× {$vialStr}";
    }
}
