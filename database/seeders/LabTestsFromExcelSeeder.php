<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TestHead;
use App\Models\Test;

class LabTestsFromExcelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Remove all non-Pathology lab tests (Radiology, legacy, etc.)
        Test::where(function ($query) {
            $query->where('category', '!=', 'Pathology')->orWhereNull('category');
        })->delete();
        TestHead::where(function ($query) {
            $query->where('category', '!=', 'Pathology')->orWhereNull('category');
        })->delete();

        // Refresh Pathology data from Excel
        Test::where('category', 'Pathology')->delete();
        TestHead::where('category', 'Pathology')->delete();

        $jsonPath = __DIR__ . '/tests_data.json';

        if (!file_exists($jsonPath)) {
            $this->command->error("Data file not found at: $jsonPath");
            return;
        }

        $jsonData = file_get_contents($jsonPath);
        // Remove BOM if present
        $jsonData = preg_replace('/^\xEF\xBB\xBF/', '', $jsonData);
        $tests = json_decode($jsonData, true);

        if (!$tests) {
            $this->command->error("Failed to decode JSON data: " . json_last_error_msg());
            return;
        }

        $count = 0;
        foreach ($tests as $data) {
            $excelId = trim($data['id']);
            $name = trim($data['name']);
            $price = trim($data['price']);
            $type = trim($data['type']);
            $headName = trim($data['head']);
            $reportStr = trim($data['report']);

            // Get or Create Test Head
            $testHead = TestHead::firstOrCreate(
                ['name' => $headName, 'category' => 'Pathology'],
                ['category' => 'Pathology']
            );

            // Parse Report Time to Hours
            $reportTime = $this->parseReportTime($reportStr);

            // Resolve sample vial info from test name + head (CLSI / lab standards)
            $vialInfo = PathologyVialMapper::resolve($name, $headName);

            // Create Test
            Test::create([
                'test_id' => 'PATH-' . str_pad($excelId, 4, '0', STR_PAD_LEFT),
                'name' => $name,
                'price' => floatval($price),
                'type' => $type,
                'test_head_id' => $testHead->id,
                'category' => 'Pathology',
                'priority' => 'Routine',
                'report_time' => $reportTime,
                'report_format' => 'Quantitative',
                'sample_vial' => $vialInfo['sample_vial'],
                'sample_expiry_hours' => $vialInfo['sample_expiry_hours'],
                'vials_required' => $vialInfo['vials_required'],
            ]);

            $count++;
        }

        $this->command->info("Successfully seeded $count tests into Pathology category with vial information.");
    }

    /**
     * Parse report time string to hours
     */
    private function parseReportTime($str): int
    {
        $str = strtolower($str);
        
        if ($str == 'same day') return 24;
        if ($str == 'next day') return 48;
        
        if (preg_match('/(\d+)\s*days?/', $str, $matches)) {
            return intval($matches[1]) * 24;
        }
        
        if (preg_match('/(\d+)\s*weeks?/', $str, $matches)) {
            return intval($matches[1]) * 7 * 24;
        }
        
        if (preg_match('/(\d+)\s*hrs?/', $str, $matches)) {
            return intval($matches[1]);
        }

        return 24; // Default 24 hours
    }
}
