<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Test;
use App\Models\TestParticular;

class TestParticularsFromExcelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
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
            $this->command->error("Failed to decode JSON data: " . json_last_error_msg());
            return;
        }

        $count = 0;
        $notFound = [];

        foreach ($particularsArr as $data) {
            $excelTestId = trim($data['test_excel_id'] ?? '');
            $testName = trim($data['test_name'] ?? '');
            $particularName = trim($data['name'] ?? '');
            $unit = trim($data['unit'] ?? '');
            $male = trim($data['male'] ?? '');
            $female = trim($data['female'] ?? '');
            $child = trim($data['child'] ?? '');

            if (empty($particularName)) continue;

            $test = null;

            // 1. Try by PATH-xxxx ID if ID exists
            if (!empty($excelTestId) && is_numeric($excelTestId)) {
                $testIdStr = 'PATH-' . str_pad($excelTestId, 4, '0', STR_PAD_LEFT);
                $test = Test::where('test_id', $testIdStr)->first();
            }

            // 2. Try by Exact Name
            if (!$test && !empty($testName)) {
                $test = Test::where('name', $testName)->first();
            }

            // 3. Try by Partial Name (if testName is like "Biochemistry" it might be wrong, but let's see)
            if (!$test && !empty($testName) && strlen($testName) > 3) {
                 // Skip common category names that aren't tests
                 if (!in_array(strtolower($testName), ['biochemistry', 'hematology', 'microbiology', 'serology', 'immunology'])) {
                    $test = Test::where('name', 'like', "%$testName%")->first();
                 }
            }

            if (!$test) {
                $notFound[] = $testName ?: $particularName;
                continue;
            }

            // Parse ranges (using male range as default for numeric min/max)
            $range = $this->parseRange($male);
            
            // Build reference text
            $refText = [];
            if ($male) $refText[] = "Male: $male";
            if ($female) $refText[] = "Female: $female";
            if ($child) $refText[] = "Child: $child";
            $referenceText = implode(", ", $refText);

            // Create/Update Particular
            TestParticular::updateOrCreate(
                [
                    'test_id' => $test->id,
                    'name' => $particularName,
                ],
                [
                    'unit' => $unit,
                    'normal_range_min' => $range[0],
                    'normal_range_max' => $range[1],
                    'reference_text' => $referenceText,
                ]
            );

            $count++;
        }

        $this->command->info("Successfully seeded $count particulars.");
        
        if (!empty($notFound)) {
            $uniqueNotFound = array_unique($notFound);
            $this->command->warn("Could not find " . count($uniqueNotFound) . " tests/heads: " . implode(', ', array_slice($uniqueNotFound, 0, 5)) . "...");
        }
    }

    private function parseRange($str)
    {
        if (!$str) return [null, null];
        $str = str_replace(' ', '', $str);
        if (preg_match('/^(\d+\.?\d*)-(\d+\.?\d*)$/', $str, $matches)) return [floatval($matches[1]), floatval($matches[2])];
        if (preg_match('/^<(\d+\.?\d*)$/', $str, $matches)) return [null, floatval($matches[1])];
        if (preg_match('/^>(\d+\.?\d*)$/', $str, $matches)) return [floatval($matches[1]), null];
        return [null, null];
    }
}
