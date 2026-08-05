<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TestParticular;

echo 'patient_type: ' . TestParticular::whereNotNull('patient_type')->count() . PHP_EOL;
echo 'reference_range_text: ' . TestParticular::whereNotNull('reference_range_text')->count() . PHP_EOL;
echo 'critical ranges: ' . TestParticular::where(function ($q) {
    $q->whereNotNull('critical_range_min')->orWhereNotNull('critical_range_max');
})->count() . PHP_EOL;
echo 'interpretation_name: ' . TestParticular::whereNotNull('interpretation_name')->where('interpretation_name', '!=', '')->count() . PHP_EOL;

$p = TestParticular::whereNotNull('reference_range_text')->where('name', 'like', '%AFB%')->first();
if ($p) {
    echo PHP_EOL . 'Sample AFB particular:' . PHP_EOL;
    echo '  name: ' . $p->name . PHP_EOL;
    echo '  patient_type: ' . $p->patient_type . PHP_EOL;
    echo '  normal: ' . ($p->formattedNumericRange() ?: '—') . PHP_EOL;
    echo '  critical: ' . ($p->formattedCriticalRange() ?: '—') . PHP_EOL;
    echo '  words: ' . $p->referenceRangeText() . PHP_EOL;
}

$p2 = TestParticular::where('name', '17-OH Progesterone')->where('patient_type', 'Male')->first();
if ($p2) {
    echo PHP_EOL . 'Sample numeric particular:' . PHP_EOL;
    echo '  normal: ' . $p2->formattedNumericRange() . PHP_EOL;
    echo '  critical: ' . ($p2->formattedCriticalRange() ?: '—') . PHP_EOL;
}
