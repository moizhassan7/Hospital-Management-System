<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$keyTests = [
    'cbc', 'lft', 'rft', 'lipid', 'pt', 'inr', 'urine', 'semen', 'blood group',
    'cross match', 'tft', 'thyroid', 'electrolyte', 'coagulation', 'widal', 'torch',
    'hepatitis screening', 'dengue', 'hba1c', 'ogtt', 'csf examination',
];

$tests = App\Models\Test::where('category', 'Pathology')
    ->where('is_active', true)
    ->with(['testParticulars' => fn ($q) => $q->orderBy('sort_order')])
    ->get();

echo "=== KEY PANEL TESTS ===" . PHP_EOL;
foreach ($tests as $test) {
    $norm = strtolower($test->name);
    $match = false;
    foreach ($keyTests as $k) {
        if (str_contains($norm, $k)) {
            $match = true;
            break;
        }
    }
    if (! $match) {
        continue;
    }
    $parts = $test->testParticulars;
    $withUnit = $parts->whereNotNull('unit')->where('unit', '!=', '')->count();
    $withRef = $parts->filter(fn ($p) => $p->normal_range_min !== null || $p->normal_range_max !== null || $p->reference_text)->count();
    echo sprintf("%-45s id=%-6d params=%-2d unit=%-2d ref=%-2d\n", $test->name, $test->id, $parts->count(), $withUnit, $withRef);
    foreach ($parts as $p) {
        echo "  - {$p->name} | unit=" . ($p->unit ?: '—') . " | ref=" . ($p->reference_text ?: (($p->normal_range_min ?? '—') . '-' . ($p->normal_range_max ?? '—'))) . PHP_EOL;
    }
    echo PHP_EOL;
}

// Quantitative single tests missing unit
$missingUnit = App\Models\Test::where('category', 'Pathology')
    ->where('is_active', true)
    ->withCount('testParticulars')
    ->having('test_particulars_count', 1)
    ->with(['testParticulars'])
    ->get()
    ->filter(function ($t) {
        $p = $t->testParticulars->first();
        if (! $p) {
            return false;
        }
        $name = strtolower($t->name);
        // Skip qualitative
        if (preg_match('/screen|elisa|pcr|culture|c\/s|biopsy|fnac|cytology|smear|gram|afb|widal|cross match|blood group|hiv|hcv|hbs|vdrl|pregnancy|malaria|brucella|ana |igg|igm|antigen|antibod/i', $name)) {
            return false;
        }
        return empty($p->unit) && $p->normal_range_min === null && $p->normal_range_max === null && empty($p->reference_text);
    });

echo "=== SINGLE QUANT TESTS MISSING UNIT/REF (" . $missingUnit->count() . ") ===" . PHP_EOL;
foreach ($missingUnit->take(30) as $t) {
    echo $t->id . "\t" . $t->name . PHP_EOL;
}
