<?php

namespace Database\Seeders;

use App\Models\Test;
use App\Models\TestParticular;
use Database\Seeders\Support\LabParticularStandards;
use Illuminate\Database\Seeder;

class TestParticularsFromExcelSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__ . '/particulars_data.json';

        if (!file_exists($jsonPath)) {
            $this->command->error("Data file not found at: $jsonPath");

            return;
        }

        $jsonData = file_get_contents($jsonPath);
        $jsonData = preg_replace('/^\xEF\xBB\xBF/', '', $jsonData);
        $particularsArr = json_decode($jsonData, true);

        if (!$particularsArr) {
            $this->command->error('Failed to decode JSON data: ' . json_last_error_msg());

            return;
        }

        $grouped = [];
        $notFound = [];

        foreach ($particularsArr as $index => $data) {
            $excelTestId = trim($data['test_excel_id'] ?? '');
            $testName = trim($data['test_name'] ?? '');
            $particularName = trim($data['name'] ?? '');

            if ($particularName === '') {
                continue;
            }

            $test = $this->resolveTest($excelTestId, $testName);

            if (!$test) {
                $notFound[] = $testName ?: $particularName;
                continue;
            }

            $grouped[$test->id][] = [
                'test' => $test,
                'data' => $data,
                'name' => $particularName,
                '_index' => $index,
            ];
        }

        $count = 0;

        foreach ($grouped as $testId => $rows) {
            $orderedRows = LabParticularStandards::assignReportOrders(
                array_map(fn (array $row) => ['name' => $row['name'], '_index' => $row['_index']], $rows)
            );

            $sortByIndex = [];
            foreach ($orderedRows as $ordered) {
                $sortByIndex[$ordered['_index']] = $ordered['sort_order'];
            }

            foreach ($rows as $row) {
                $data = $row['data'];
                $particularName = $row['name'];
                $male = trim($data['male'] ?? '');
                $female = trim($data['female'] ?? '');
                $child = trim($data['child'] ?? '');
                $unit = trim($data['unit'] ?? '');

                $range = $this->parseRange($male);
                $referenceText = $this->buildReferenceText($male, $female, $child);
                $meta = LabParticularStandards::metadataFor($particularName);

                TestParticular::updateOrCreate(
                    [
                        'test_id' => $testId,
                        'name' => $particularName,
                    ],
                    [
                        'result_key' => $meta['result_key'],
                        'unit' => $unit !== '' ? $unit : null,
                        'normal_range_min' => $range[0],
                        'normal_range_max' => $range[1],
                        'critical_range_min' => $meta['critical_range_min'],
                        'critical_range_max' => $meta['critical_range_max'],
                        'reference_text' => $referenceText,
                        'remarks' => $meta['remarks'],
                        'formula' => $meta['formula'],
                        'is_calculated' => $meta['is_calculated'],
                        'sort_order' => $sortByIndex[$row['_index']] ?? 1,
                        'is_active' => true,
                    ]
                );

                $count++;
            }
        }

        $this->command->info("Successfully seeded $count particulars with international report order.");

        if ($notFound !== []) {
            $uniqueNotFound = array_unique($notFound);
            $this->command->warn(
                'Could not find ' . count($uniqueNotFound) . ' tests: '
                . implode(', ', array_slice($uniqueNotFound, 0, 5)) . '...'
            );
        }
    }

    private function buildReferenceText(string $male, string $female, string $child): ?string
    {
        $refText = [];
        if ($male !== '') {
            $refText[] = "Male: $male";
        }
        if ($female !== '') {
            $refText[] = "Female: $female";
        }
        if ($child !== '') {
            $refText[] = "Child: $child";
        }

        return $refText === [] ? null : implode(' | ', $refText);
    }

    private function resolveTest(string $excelTestId, string $testName): ?Test
    {
        if ($excelTestId !== '' && is_numeric($excelTestId)) {
            $numericId = (int) $excelTestId;

            $test = Test::find($numericId);
            if ($test) {
                return $test;
            }

            $test = Test::where('test_id', $excelTestId)->first();
            if ($test) {
                return $test;
            }

            $legacyId = 'PATH-' . str_pad($excelTestId, 4, '0', STR_PAD_LEFT);
            $test = Test::where('test_id', $legacyId)->first();
            if ($test) {
                return $test;
            }
        }

        if ($testName !== '') {
            $test = Test::whereRaw('LOWER(name) = ?', [strtolower($testName)])->first();
            if ($test) {
                return $test;
            }

            if (strlen($testName) > 3
                && !in_array(strtolower($testName), ['biochemistry', 'hematology', 'microbiology', 'serology', 'immunology'], true)) {
                return Test::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($testName) . '%'])->first();
            }
        }

        return null;
    }

    /**
     * @return array{0: float|null, 1: float|null}
     */
    private function parseRange(?string $str): array
    {
        if (!$str) {
            return [null, null];
        }

        $str = str_replace(' ', '', $str);

        if (preg_match('/^(\d+\.?\d*)-(\d+\.?\d*)$/', $str, $matches)) {
            return [floatval($matches[1]), floatval($matches[2])];
        }

        if (preg_match('/^<(\d+\.?\d*)$/', $str, $matches)) {
            return [null, floatval($matches[1])];
        }

        if (preg_match('/^>(\d+\.?\d*)$/', $str, $matches)) {
            return [floatval($matches[1]), null];
        }

        return [null, null];
    }
}
