<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$lookup = app(App\Services\LabPatientLookupService::class);
$result = $lookup->findOrImportByLabRegNo('1');

echo 'patient=' . ($result['patient']?->patient_name ?? 'null') . PHP_EOL;
echo 'lab_reg=' . ($result['patient']?->lab_registration_no ?? 'null') . PHP_EOL;
echo 'imported=' . ($result['imported'] ? 'yes' : 'no') . PHP_EOL;
echo 'tests=' . count($result['patient']?->getSelectedTestsArray() ?? []) . PHP_EOL;
echo 'error=' . ($result['error'] ?? 'none') . PHP_EOL;

if ($result['patient']) {
    $url = app(App\Services\PathologyReportService::class)->getOnlineReportUrl($result['patient']->id, $result['patient']->getSelectedTestsArray()[0]['id'] ?? 0);
    echo 'report_url=' . $url . PHP_EOL;
}
