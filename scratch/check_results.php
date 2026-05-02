<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TestResult;
use App\Models\TestParticular;

$lab_patient_id = 1;
$test_id = 26;

$results = TestResult::where('laboratory_patient_id', $lab_patient_id)
                     ->where('test_id', $test_id)
                     ->get();

echo "Results found: " . $results->count() . "\n";
foreach ($results as $result) {
    $particular = TestParticular::find($result->test_particular_id);
    echo "ID: " . $result->id . " | Particular: " . ($particular ? $particular->name : 'Unknown') . " | Value: " . substr($result->result_value, 0, 50) . "...\n";
}
