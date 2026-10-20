<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$svc = app(App\Services\DoctorShareService::class);

foreach ([1 => 'John', 2 => 'Sarah', 5 => 'William'] as $id => $name) {
    $bal = $svc->balanceSummary($id);
    $calc = $svc->calculateShare($id);
    echo "=== {$name} (id={$id}) ===" . PHP_EOL;
    echo json_encode($bal) . PHP_EOL;
    foreach ($calc['rows'] as $row) {
        if ($row['module'] === 'IPD Discharge') {
            echo "  [{$row['module']}] {$row['procedure_name']} total={$row['total_amount']} share={$row['doctor_share_amount']}" . PHP_EOL;
        }
    }
}

echo PHP_EOL . 'Payable doctors in ledger:' . PHP_EOL;
foreach ($svc->payableDoctors() as $d) {
    $s = $svc->balanceSummary($d->id);
    if ($s['total_share'] > 0 || $s['total_paid'] > 0) {
        echo "  {$d->name} ({$d->employee_group}) share={$s['total_share']} pending={$s['pending']}" . PHP_EOL;
    }
}
