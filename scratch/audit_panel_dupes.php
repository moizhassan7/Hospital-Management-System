<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$patterns = ['cbc', 'lft', 'liver function', 'urine examination', 'urine complete', 'lipid profile', 'rft', 'semen'];

$tests = App\Models\Test::where('category', 'Pathology')
    ->where('is_active', true)
    ->where(function ($q) use ($patterns) {
        foreach ($patterns as $p) {
            $q->orWhereRaw('LOWER(name) LIKE ?', ['%' . $p . '%']);
        }
    })
    ->with(['testParticulars' => fn ($q) => $q->orderBy('sort_order')])
    ->orderBy('name')
    ->get();

foreach ($tests as $test) {
    $parts = $test->testParticulars;
    $names = $parts->pluck('name')->map(fn ($n) => strtolower(trim($n)))->all();
    $dupes = array_diff_assoc($names, array_unique($names));
    echo "=== {$test->name} (id={$test->id}) count={$parts->count()} dupes=" . count(array_unique($dupes)) . " ===" . PHP_EOL;
    foreach ($parts as $i => $p) {
        echo sprintf("  %2d. %s\n", $i + 1, $p->name);
    }
    echo PHP_EOL;
}
