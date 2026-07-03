<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\LaboratoryPatient;
use App\Models\Test;
use App\Services\PathologyReportService;

$labPatient = LaboratoryPatient::query()->latest('id')->first();
$test = Test::with(['testHead', 'testParticulars'])->where('category', 'Pathology')->first();

if (! $labPatient || ! $test) {
    echo "No sample data\n";
    exit(0);
}

$data = app(PathologyReportService::class)->buildReportData($labPatient->id, $test->id);
$html = view('laboratory.print_report', $data)->render();
echo 'Rendered ' . strlen($html) . " bytes\n";
