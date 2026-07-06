<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tests = App\Models\Test::where('category', 'Pathology')
    ->where('is_active', true)
    ->whereDoesntHave('testParticulars')
    ->with('testHead:id,name')
    ->orderBy('name')
    ->get(['id', 'name', 'test_id', 'test_head_id']);

file_put_contents(__DIR__ . '/zero_particular_tests.json', json_encode(
    $tests->map(fn ($t) => [
        'id' => $t->id,
        'test_id' => $t->test_id,
        'name' => $t->name,
        'head' => $t->testHead->name ?? null,
    ])->values()->all(),
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
));

echo 'Total: ' . $tests->count() . PHP_EOL;
foreach ($tests as $test) {
    echo $test->id . "\t" . $test->test_id . "\t" . $test->name . "\t" . ($test->testHead->name ?? '') . PHP_EOL;
}
