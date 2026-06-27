<?php

namespace Database\Seeders;

use App\Models\Test;
use App\Models\TestParticular;
use App\Models\TestResult;
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

        $egfrRemarks = "STAGE 1: GFR equal to or more than 90 (slight renal damage with normal or increased GFR).\n"
            . "STAGE 2: GFR 60-89 (mild decrease in GFR with mild kidney damage).\n"
            . "STAGE 3: GFR 30-59 (moderate decrease in GFR with moderate kidney damage).\n"
            . "STAGE 4: GFR 15-29 (severe decrease in GFR with severe kidney damage).\n"
            . "STAGE 5: GFR less than 15 (kidney failure - consider dialysis or renal transplant).";

        $this->createParticulars($test->id, [
            ['Urea', 'urea', 'mg/dL', 15, 39, null, null, null, false, 1],
            ['Creatinine', 'creatinine', 'mg/dL', 0.70, 1.40, null, null, null, false, 2],
            ['BUN (Blood Urea Nitrogen)', 'bun', 'mg/dL', 8, 25, null, null, 'bun_from_urea', true, 3],
            ['Estimated Glomerular Filtration Rate (eGFR)', 'egfr', 'mL/min/1.73m²', null, null, null, $egfrRemarks, 'egfr_mdrd', true, 4],
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

        $sgotRemarks = "Children:\n1 - 3 years < 50\n4 - 6 years < 45\n7 - 9 years < 40\n10 - 12 years < 38\n13 - 15 years < 36\n16 - 18 years < 35";
        $alpRemarks = "Child < 645";
        $proteinRemarks = "New born 4.6 - 7.0";

        $this->createParticulars($test->id, [
            ['Bilirubin Total', 'bilirubin_total', 'mg/dL', 0.3, 1.0, null, null, null, false, 1],
            ['Bilirubin Direct', 'bilirubin_direct', 'mg/dL', 0.0, 0.2, null, null, null, false, 2],
            ['Bilirubin Indirect', 'bilirubin_indirect', 'mg/dL', 0.2, 0.8, null, null, 'indirect_bilirubin', true, 3],
            ['SGOT (AST)', 'sgot', 'U/L', 0, 40, null, $sgotRemarks, null, false, 4],
            ['SGPT (ALT)', 'sgpt', 'U/L', 0, 41, null, null, null, false, 5],
            ['Alkaline Phosphatase (ALP)', 'alp', 'U/L', 40, 150, null, $alpRemarks, null, false, 6],
            ['Total Protein', 'total_protein', 'g/dL', 6.4, 8.3, null, $proteinRemarks, null, false, 7],
            ['Albumin', 'albumin', 'g/dL', 3.5, 5.2, null, null, null, false, 8],
            ['Globulins', 'globulins', 'g/dL', 2.0, 3.5, null, null, null, false, 9],
            ['A/G Ratio', 'ag_ratio', 'Ratio', 1.0, 2.0, null, null, 'ag_ratio', true, 10],
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
            ['PT', 'pt', 'Sec.', 11, 14, null, null, null, false, 1],
            ['Control', 'control', 'Sec.', 11, 14, null, null, null, false, 2],
            ['INR', 'inr', 'Ratio', 0.8, 1.2, null, null, 'inr_from_pt', true, 3],
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

    private function createParticulars(int $testId, array $rows): void
    {
        foreach ($rows as $row) {
            [$name, $key, $unit, $min, $max, $referenceText, $remarks, $formula, $isCalculated, $sort] = $row;

            TestParticular::create([
                'test_id' => $testId,
                'name' => $name,
                'result_key' => $key,
                'unit' => $unit,
                'normal_range_min' => $min,
                'normal_range_max' => $max,
                'reference_text' => $referenceText,
                'remarks' => $remarks,
                'formula' => $formula,
                'is_calculated' => $isCalculated,
                'sort_order' => $sort,
            ]);
        }
    }
}
