<?php

namespace Database\Seeders;

use App\Models\Test;
use App\Models\TestParticular;
use App\Models\TestResult;
use Database\Seeders\Support\LabParticularStandards;
use Illuminate\Database\Seeder;

class PathologyPanelSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRft();
        $this->seedLft();
        $this->seedPtInr();
    }

    private function seedRft(): void
    {
        $test = $this->findDesktopTest(37, ['%rft%', '%renal function%']);

        if (!$test) {
            return;
        }

        TestResult::where('test_id', $test->id)->delete();
        TestParticular::where('test_id', $test->id)->delete();

        $this->createParticulars($test->id, [
            ['Urea', 'urea', 'mg/dL', 15, 39, null, null, false, 1],
            ['Creatinine', 'creatinine', 'mg/dL', 0.70, 1.40, null, null, false, 2],
            ['BUN (Blood Urea Nitrogen)', 'bun', 'mg/dL', 8, 25, null, null, true, 3],
            ['Estimated Glomerular Filtration Rate (eGFR)', 'egfr', 'mL/min/1.73m²', null, null, null, null, true, 4],
        ]);
    }

    private function seedLft(): void
    {
        $test = $this->findDesktopTest(138, ['%lft%', '%liver function%']);

        if (!$test) {
            return;
        }

        TestResult::where('test_id', $test->id)->delete();
        TestParticular::where('test_id', $test->id)->delete();

        $this->createParticulars($test->id, [
            ['Bilirubin Total', 'bilirubin_total', 'mg/dL', 0.3, 1.0, null, null, false, 1],
            ['Bilirubin Direct', 'bilirubin_direct', 'mg/dL', 0.0, 0.2, null, null, false, 2],
            ['Bilirubin Indirect', 'bilirubin_indirect', 'mg/dL', 0.2, 0.8, null, null, true, 3],
            ['SGOT (AST)', 'sgot', 'U/L', 0, 40, null, null, false, 4],
            ['SGPT (ALT)', 'sgpt', 'U/L', 0, 41, null, null, false, 5],
            ['Alkaline Phosphatase (ALP)', 'alp', 'U/L', 40, 150, null, null, false, 6],
            ['Total Protein', 'total_protein', 'g/dL', 6.4, 8.3, null, null, false, 7],
            ['Albumin', 'albumin', 'g/dL', 3.5, 5.2, null, null, false, 8],
            ['Globulins', 'globulins', 'g/dL', 2.0, 3.5, null, null, false, 9],
            ['A/G Ratio', 'ag_ratio', 'Ratio', 1.0, 2.0, null, null, true, 10],
        ]);
    }

    private function seedPtInr(): void
    {
        $test = $this->findDesktopTest(63, ['%pt%inr%', '%pt / inr%']);

        if (!$test) {
            return;
        }

        TestResult::where('test_id', $test->id)->delete();
        TestParticular::where('test_id', $test->id)->delete();

        $this->createParticulars($test->id, [
            ['PT', 'pt', 'Sec.', 11, 14, null, null, false, 1],
            ['Control', 'control', 'Sec.', 11, 14, null, null, false, 2],
            ['INR', 'inr', 'Ratio', 0.8, 1.2, null, null, true, 3],
        ]);
    }

    private function findDesktopTest(int $desktopId, array $namePatterns = []): ?Test
    {
        $test = Test::where('category', 'Pathology')->find($desktopId);

        if ($test) {
            return $test;
        }

        foreach ($namePatterns as $pattern) {
            $test = Test::where('category', 'Pathology')
                ->whereRaw('LOWER(name) LIKE ?', [strtolower($pattern)])
                ->first();

            if ($test) {
                return $test;
            }
        }

        return null;
    }

    /**
     * @param  list<array{0: string, 1: string, 2: string, 3: float|null, 4: float|null, 5: string|null, 6: string|null, 7: bool, 8: int}>  $rows
     */
    private function createParticulars(int $testId, array $rows): void
    {
        foreach ($rows as $row) {
            [$name, $key, $unit, $min, $max, $referenceText, $remarks, $isCalculated, $sort] = $row;
            $meta = LabParticularStandards::metadataFor($name);

            TestParticular::create([
                'test_id' => $testId,
                'name' => $name,
                'result_key' => $key,
                'unit' => $unit,
                'normal_range_min' => $min,
                'normal_range_max' => $max,
                'critical_range_min' => $meta['critical_range_min'],
                'critical_range_max' => $meta['critical_range_max'],
                'reference_text' => $referenceText,
                'remarks' => $remarks ?? $meta['remarks'],
                'formula' => $meta['formula'],
                'is_calculated' => $isCalculated || $meta['is_calculated'],
                'sort_order' => $sort,
                'is_active' => true,
            ]);
        }
    }
}
