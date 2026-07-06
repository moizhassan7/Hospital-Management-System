<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tests = App\Models\Test::where('category', 'Pathology')
    ->where('is_active', true)
    ->withCount('testParticulars')
    ->with('testHead:id,name')
    ->orderBy('name')
    ->get(['id', 'name', 'test_id', 'desktop_test_id', 'test_head_id']);

$zero = $tests->where('test_particulars_count', 0);
$single = $tests->where('test_particulars_count', 1);
$fromDesktop = $tests->filter(fn ($t) => ! empty($t->desktop_test_id) || (is_numeric($t->test_id) && (int) $t->test_id < 90000));

echo '=== SUMMARY ===' . PHP_EOL;
echo 'Total active pathology tests: ' . $tests->count() . PHP_EOL;
echo 'With 0 particulars: ' . $zero->count() . PHP_EOL;
echo 'With exactly 1 particular: ' . $single->count() . PHP_EOL;
echo 'Likely from desktop catalog: ' . $fromDesktop->count() . PHP_EOL;
echo 'Desktop-sourced with 0 particulars: ' . $fromDesktop->where('test_particulars_count', 0)->count() . PHP_EOL;
echo PHP_EOL;

if ($zero->isNotEmpty()) {
    echo '=== ZERO PARTICULARS ===' . PHP_EOL;
    foreach ($zero as $t) {
        echo $t->id . "\t" . $t->name . "\t" . ($t->testHead->name ?? '') . PHP_EOL;
    }
    echo PHP_EOL;
}

// Single-parameter tests that should be panels (international standard panels)
$panelPatterns = [
    'cbc', 'complete blood', 'lft', 'liver function', 'rft', 'renal function', 'kft',
    'lipid', 'tft', 'thyroid', 'coagulation', 'pt inr', 'pt/inr', 'electrolyte',
    'urine', 'stool', 'semen', 'csf', 'abg', 'torch', 'widal', 'dengue',
    'hepatitis screening', 'cross match', 'blood group', 'ogtt', 'gtt',
];

$weakPanels = $single->filter(function ($t) use ($panelPatterns) {
    $norm = strtolower($t->name);
    foreach ($panelPatterns as $p) {
        if (str_contains($norm, $p)) {
            return true;
        }
    }
    return false;
});

echo '=== LIKELY INCOMPLETE PANELS (only 1 parameter) ===' . PHP_EOL;
echo 'Count: ' . $weakPanels->count() . PHP_EOL;
foreach ($weakPanels->take(40) as $t) {
    echo $t->id . "\t" . $t->name . "\t" . ($t->testHead->name ?? '') . PHP_EOL;
}
if ($weakPanels->count() > 40) {
    echo '... and ' . ($weakPanels->count() - 40) . ' more' . PHP_EOL;
}

// Export full audit
file_put_contents(__DIR__ . '/particulars_audit.json', json_encode([
    'zero' => $zero->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'head' => $t->testHead->name ?? null])->values(),
    'weak_panels' => $weakPanels->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'head' => $t->testHead->name ?? null, 'count' => $t->test_particulars_count])->values(),
    'stats' => [
        'total' => $tests->count(),
        'zero' => $zero->count(),
        'single' => $single->count(),
        'weak_panels' => $weakPanels->count(),
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo PHP_EOL . 'Audit saved to scratch/particulars_audit.json' . PHP_EOL;
