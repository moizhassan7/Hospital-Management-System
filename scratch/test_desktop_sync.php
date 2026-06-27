<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'pdo_sqlsrv: ' . (extension_loaded('pdo_sqlsrv') ? 'yes' : 'NO') . PHP_EOL;

try {
    $count = App\Models\Desktop\DesktopRegTest::count();
    echo "reg_test total rows: {$count}" . PHP_EOL;

    $mr12 = App\Models\Desktop\DesktopRegTest::where('Mr_No', 12)->count();
    echo "Mr_No=12 rows: {$mr12}" . PHP_EOL;

    echo PHP_EOL . "Rows with Mr_No set:" . PHP_EOL;
    foreach (App\Models\Desktop\DesktopRegTest::whereNotNull('Mr_No')->orderByDesc('id')->limit(10)->get() as $row) {
        echo sprintf(
            "  MR=%s | %s | test=%s | inv=%s\n",
            $row->Mr_No,
            $row->p_name ?? '-',
            $row->test_name ?? '-',
            $row->inv ?? '-'
        );
    }

    echo PHP_EOL . "Rows with p_name set:" . PHP_EOL;
    foreach (App\Models\Desktop\DesktopRegTest::whereNotNull('p_name')->orderByDesc('id')->limit(5)->get() as $row) {
        echo sprintf(
            "  MR=%s | %s | test=%s | inv=%s\n",
            $row->Mr_No ?? 'NULL',
            $row->p_name,
            $row->test_name ?? '-',
            $row->inv ?? '-'
        );
    }

    $service = app(App\Services\DesktopBookingSyncService::class);
    $result = $service->syncLatestBooking('12');
    echo 'imported: ' . ($result['imported'] ? 'yes' : 'no') . PHP_EOL;
    echo 'patient: ' . ($result['patient']?->patient_name ?? 'null') . PHP_EOL;
    echo 'error: ' . ($service->getLastError() ?? 'none') . PHP_EOL;
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
}
