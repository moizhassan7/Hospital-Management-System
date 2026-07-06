<?php

namespace App\Console\Commands;

use App\Models\Test;
use Illuminate\Console\Command;

class VerifyTestParticulars extends Command
{
    protected $signature = 'lab:verify-particulars {--json : Output JSON report}';

    protected $description = 'Verify all desktop pathology tests have particulars and flag incomplete panels';

    public function handle(): int
    {
        $tests = Test::where('category', 'Pathology')
            ->where('is_active', true)
            ->withCount('testParticulars')
            ->with('testHead:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'test_id', 'desktop_test_id', 'test_head_id']);

        $zero = $tests->where('test_particulars_count', 0);
        $fromDesktop = $tests->filter(fn ($t) => ! empty($t->desktop_test_id) || (is_numeric($t->test_id) && (int) $t->test_id < 90000));
        $desktopZero = $fromDesktop->where('test_particulars_count', 0);

        $panelPatterns = [
            'cbc', 'complete blood', 'lft', 'liver function', 'rft', 'renal', 'lipid',
            'tft', 'thyroid', 'coagulation', 'electrolyte', 'urine', 'cross match',
            'culture', 'c/s', 'torch', 'widal', 'ogtt', 'hepatitis screening', 'dengue',
        ];

        $weakPanels = $tests->filter(function ($t) use ($panelPatterns) {
            if ($t->test_particulars_count !== 1) {
                return false;
            }
            $norm = strtolower($t->name);
            // Single-analyte tests that legitimately have one parameter
            if (preg_match('/\b(igg|igm|iga|tpo|ketone|antigen|antibod)\b/i', $t->name)) {
                return false;
            }
            foreach ($panelPatterns as $p) {
                if (str_contains($norm, $p)) {
                    return true;
                }
            }

            return false;
        });

        if ($this->option('json')) {
            $this->line(json_encode([
                'total' => $tests->count(),
                'zero_particulars' => $zero->count(),
                'desktop_total' => $fromDesktop->count(),
                'desktop_zero' => $desktopZero->count(),
                'weak_panels' => $weakPanels->count(),
                'zero_tests' => $zero->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])->values(),
                'weak_panel_tests' => $weakPanels->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])->values(),
            ], JSON_PRETTY_PRINT));

            return $desktopZero->isEmpty() ? self::SUCCESS : self::FAILURE;
        }

        $this->info('=== Pathology Particulars Verification ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total active pathology tests', $tests->count()],
                ['Tests with 0 particulars', $zero->count()],
                ['Desktop-sourced tests', $fromDesktop->count()],
                ['Desktop tests with 0 particulars', $desktopZero->count()],
                ['Likely incomplete panels (1 param)', $weakPanels->count()],
            ]
        );

        if ($zero->isNotEmpty()) {
            $this->error('Tests missing ALL particulars:');
            foreach ($zero as $t) {
                $this->line("  [{$t->id}] {$t->name}");
            }
        } else {
            $this->info('PASS: No test has zero particulars.');
        }

        if ($desktopZero->isNotEmpty()) {
            $this->error('FAIL: Desktop tests with zero particulars found.');

            return self::FAILURE;
        }

        if ($weakPanels->isNotEmpty()) {
            $this->warn('Advisory: ' . $weakPanels->count() . ' panel-type tests still have only 1 parameter:');
            foreach ($weakPanels->take(15) as $t) {
                $this->line("  [{$t->id}] {$t->name}");
            }
            if ($weakPanels->count() > 15) {
                $this->line('  ... and ' . ($weakPanels->count() - 15) . ' more');
            }
        }

        return $zero->isEmpty() ? self::SUCCESS : self::FAILURE;
    }
}
