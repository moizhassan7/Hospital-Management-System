<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cc = App\Models\CollectionCenter::where('code', 'CC1')->firstOrFail();
$a = app(App\Services\Lims\LabNumberAllocator::class);
$r1 = $a->allocate($cc->id);
$r2 = $a->allocate($cc->id);
$r3 = $a->allocate($cc->id);
echo "CC1: {$r1['lab_number']} -> {$r2['lab_number']} -> {$r3['lab_number']}\n";

$main = App\Models\CollectionCenter::where('code', 'MAIN')->firstOrFail();
$m1 = $a->allocate($main->id);
$m2 = $a->allocate($main->id);
echo "MAIN: {$m1['lab_number']} -> {$m2['lab_number']}\n";

// Dual-write smoke
$test = App\Models\Test::where('category', 'Pathology')->where('is_active', true)->first();
if (! $test) {
    echo "SKIP dual-write: no pathology test\n";
    exit(0);
}

$lp = App\Models\LaboratoryPatient::create([
    'mr_no' => 'LIMSSMOKE'.time(),
    'lab_registration_no' => '99',
    'patient_name' => 'LIMS Smoke Patient',
    'gender' => 'Male',
    'contact_no' => '03001234567',
    'age' => 30,
    'self_referred' => true,
    'selected_tests' => [[
        'id' => $test->id,
        'name' => $test->name,
        'price' => (float) $test->price,
        'carry_out' => true,
        'status' => 'Pending',
        'sample_status' => 'not_collected',
    ]],
    'sub_total' => (float) $test->price,
    'discount' => 0,
    'grand_total' => (float) $test->price,
    'paid_amount' => (float) $test->price,
    'due_amount' => 0,
    'lab_share_total' => 0,
    'hospital_share_total' => 0,
    'previous_due' => 0,
    'status' => 'Pending',
]);

$booking = app(App\Services\Lims\LimsBookingSync::class)->syncFromLaboratoryPatient($lp);
echo "Booking id={$booking->id} lab_number={$booking->lab_number} items={$booking->items->count()} invoice={$booking->invoice?->invoice_no} paid={$booking->invoice?->paid_total}\n";
echo "OK\n";
