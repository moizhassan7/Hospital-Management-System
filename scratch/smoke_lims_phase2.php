<?php

/**
 * Phase 2 smoke: batch create → add samples → dispatch → in-transit → partial receive.
 * Run: php scratch/smoke_lims_phase2.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CollectionCenter;
use App\Models\LaboratoryPatient;
use App\Models\LabSampleVial;
use App\Models\LimsBooking;
use App\Models\LimsSample;
use App\Models\LimsSampleBatch;
use App\Models\LimsSampleBatchItem;
use App\Models\Test;
use App\Models\User;
use App\Services\Lims\LimsBookingSync;
use App\Services\Lims\LimsSampleSync;
use App\Services\Lims\ManifestNumberAllocator;
use App\Services\Lims\SampleTransitService;

$cc = CollectionCenter::where('code', 'CC1')->firstOrFail();
$main = CollectionCenter::where('code', 'MAIN')->firstOrFail();

$m1 = app(ManifestNumberAllocator::class)->allocate($cc->id);
$m2 = app(ManifestNumberAllocator::class)->allocate($cc->id);
echo "Manifest: {$m1['manifest_no']} -> {$m2['manifest_no']}\n";

$tests = Test::where('category', 'Pathology')->where('is_active', true)->limit(2)->get();
$test = $tests->first();
$testB = $tests->count() > 1 ? $tests->get(1) : $test;
if (! $test) {
    echo "SKIP: no pathology test\n";
    exit(0);
}

$suffix = (string) time();
$lp = LaboratoryPatient::create([
    'mr_no' => 'LIMS2'.$suffix,
    'lab_registration_no' => '98',
    'patient_name' => 'LIMS P2 Smoke',
    'gender' => 'Male',
    'contact_no' => '03001234567',
    'age' => 30,
    'priority' => 'Routine',
    'self_referred' => true,
    'selected_tests' => array_values(array_filter([
        [
            'id' => $test->id,
            'name' => $test->name,
            'price' => (float) $test->price,
            'carry_out' => true,
            'status' => 'Pending',
            'sample_status' => 'collected',
            'sample_collected_at' => now()->toDateTimeString(),
        ],
        $testB->id !== $test->id ? [
            'id' => $testB->id,
            'name' => $testB->name,
            'price' => (float) $testB->price,
            'carry_out' => true,
            'status' => 'Pending',
            'sample_status' => 'collected',
            'sample_collected_at' => now()->toDateTimeString(),
        ] : null,
    ])),
    'sub_total' => (float) $test->price + ($testB->id !== $test->id ? (float) $testB->price : 0),
    'discount' => 0,
    'grand_total' => (float) $test->price + ($testB->id !== $test->id ? (float) $testB->price : 0),
    'paid_amount' => (float) $test->price + ($testB->id !== $test->id ? (float) $testB->price : 0),
    'due_amount' => 0,
    'lab_share_total' => 0,
    'hospital_share_total' => 0,
    'previous_due' => 0,
    'status' => 'Pending',
]);

$ccUser = new User([
    'organization_id' => $cc->organization_id,
    'collection_center_id' => $cc->id,
    'user_scope' => User::SCOPE_COLLECTION_CENTER,
    'name' => 'Smoke CC',
    'username' => 'smoke-cc',
]);
$ccUser->id = User::query()->value('id') ?? 1;

$booking = app(LimsBookingSync::class)->syncFromLaboratoryPatient($lp, $ccUser);
echo "Booking id={$booking->id} lab_number={$booking->lab_number}\n";

$vialA = LabSampleVial::create([
    'laboratory_patient_id' => $lp->id,
    'barcode' => 'P2A'.$suffix,
    'vial_type' => 'EDTA',
    'vial_number' => 1,
    'test_ids' => [$test->id],
    'collected_at' => now(),
    'status' => LabSampleVial::STATUS_COLLECTED,
]);
$vialB = LabSampleVial::create([
    'laboratory_patient_id' => $lp->id,
    'barcode' => 'P2B'.$suffix,
    'vial_type' => 'Serum',
    'vial_number' => 2,
    'test_ids' => [$testB->id],
    'collected_at' => now(),
    'status' => LabSampleVial::STATUS_COLLECTED,
]);

$sampleSync = app(LimsSampleSync::class);
$sampleA = $sampleSync->syncFromVial($vialA, $ccUser);
$sampleB = $sampleSync->syncFromVial($vialB, $ccUser);
echo "Samples A={$sampleA->id}({$sampleA->status}) B={$sampleB->id}({$sampleB->status})\n";

$transit = app(SampleTransitService::class);
$batch = $transit->createOpenBatch($ccUser, [
    'destination_site_id' => $main->id,
    'create_idempotency_key' => 'smoke-create-'.$suffix,
]);
echo "Batch id={$batch->id} manifest={$batch->manifest_no} status={$batch->status}\n";

$transit->addItem($batch, $sampleA->id, null, $ccUser);
$transit->addItem($batch, $sampleB->id, null, $ccUser);
$batch->refresh();
echo "Items={$batch->sample_count}\n";

// Idempotent create replay
$batch2 = $transit->createOpenBatch($ccUser, [
    'destination_site_id' => $main->id,
    'create_idempotency_key' => 'smoke-create-'.$suffix,
]);
echo "Create idempotent replay same id? ".((int) $batch2->id === (int) $batch->id ? 'yes' : 'no')."\n";

$dispatchKey = 'smoke-dispatch-'.$suffix;
$batch = $transit->dispatch($batch, [
    'courier_name' => 'City Runner',
    'courier_ref' => 'CR-SMOKE',
    'idempotency_key' => $dispatchKey,
], $ccUser);
echo "Dispatched status={$batch->status}\n";

$batchAgain = $transit->dispatch($batch, [
    'courier_name' => 'City Runner',
    'courier_ref' => 'CR-SMOKE',
    'idempotency_key' => $dispatchKey,
], $ccUser);
echo "Dispatch idempotent status={$batchAgain->status}\n";

$mainUser = new User([
    'organization_id' => $main->organization_id,
    'collection_center_id' => null,
    'user_scope' => User::SCOPE_MAIN_LAB,
    'name' => 'Smoke Main',
    'username' => 'smoke-main',
]);
$mainUser->id = $ccUser->id;

$transitKey = 'smoke-transit-'.$suffix;
$batch = $transit->markInTransit($batch, [
    'idempotency_key' => $transitKey,
    'location_label' => 'Courier hub',
], $mainUser);
echo "In-transit status={$batch->status}\n";

$recvKey = 'smoke-recv-'.$suffix;
$batch = $transit->receive($batch, [
    'idempotency_key' => $recvKey,
    'items' => [
        ['sample_id' => $sampleA->id, 'receive_status' => 'received'],
        ['sample_id' => $sampleB->id, 'receive_status' => 'missing', 'receive_note' => 'Not in box'],
    ],
], $mainUser);

$sampleA->refresh();
$sampleB->refresh();
$vialA->refresh();
$vialB->refresh();
$booking = LimsBooking::withoutGlobalScopes()->find($booking->id);

echo "Receive batch={$batch->status}\n";
echo "Sample A={$sampleA->status} vialA={$vialA->status}\n";
echo "Sample B={$sampleB->status} vialB={$vialB->status}\n";
echo "Booking status={$booking->status}\n";

$items = LimsSampleBatchItem::where('batch_id', $batch->id)->get()->keyBy('sample_id');
echo "Item A receive={$items[$sampleA->id]->receive_status} Item B receive={$items[$sampleB->id]->receive_status}\n";

$events = $batch->events()->orderBy('id')->pluck('event_type')->implode(',');
echo "Events: {$events}\n";

$ok = $batch->status === LimsSampleBatch::STATUS_RECEIVED
    && $sampleA->status === LimsSample::STATUS_RECEIVED
    && $vialA->status === LabSampleVial::STATUS_IN_LAB
    && $sampleB->status === LimsSample::STATUS_IN_TRANSIT
    && $vialB->status === LabSampleVial::STATUS_COLLECTED
    && $items[$sampleA->id]->receive_status === 'received'
    && $items[$sampleB->id]->receive_status === 'missing';

echo $ok ? "OK\n" : "FAIL\n";
exit($ok ? 0 : 1);
