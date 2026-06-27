<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = App\Models\Desktop\DesktopRegTest::where('inv', '1')->get();
echo "inv=1 rows: " . $rows->count() . PHP_EOL;
foreach ($rows as $r) {
    echo " id={$r->id} MR=" . ($r->Mr_No ?? 'null') . " test_id={$r->test_id} test_name=" . ($r->test_name ?: '-') . PHP_EOL;
}

try {
    $tests = Illuminate\Support\Facades\DB::connection('desktop')->table('test')->limit(5)->get();
    echo PHP_EOL . "dbo.test sample:" . PHP_EOL;
    foreach ($tests as $t) {
        echo " id={$t->id} name=" . ($t->name ?? '-') . PHP_EOL;
    }
    if ($rows->first()?->test_id) {
        $tid = $rows->first()->test_id;
        $dt = Illuminate\Support\Facades\DB::connection('desktop')->table('test')->where('id', $tid)->first();
        echo PHP_EOL . "desktop test for test_id={$tid}: " . ($dt->name ?? 'NOT FOUND') . PHP_EOL;
    }
} catch (Throwable $e) {
    echo 'test table error: ' . $e->getMessage() . PHP_EOL;
}

$service = app(App\Services\DesktopBookingSyncService::class);
foreach ($rows as $r) {
    $web = $service->resolveWebTest($r);
    echo " map id={$r->id} test_id={$r->test_id} -> " . ($web?->name ?? 'FAIL') . PHP_EOL;
}

$result = $service->syncLatestBooking('1');
echo PHP_EOL . 'sync imported=' . ($result['imported'] ? 'yes' : 'no') . ' patient=' . ($result['patient']?->patient_name ?? 'null') . PHP_EOL;
echo 'error=' . ($service->getLastError() ?? 'none') . PHP_EOL;
