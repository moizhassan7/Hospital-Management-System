<?php

/**
 * Custody actor stamp smoke: collect → dispatch(+courier) → receive.
 * Run: php scratch/smoke_custody_actors.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CollectionCenter;
use App\Models\LaboratoryPatient;
use App\Models\LabSampleVial;
use App\Models\LimsSample;
use App\Models\LimsSampleBatch;
use App\Models\LimsSampleBatchItem;
use App\Models\LimsTransitEvent;
use App\Models\Test;
use App\Models\User;
use App\Services\Lims\LimsBookingSync;
use App\Services\Lims\LimsSampleSync;
use App\Services\Lims\SampleTransitService;
use Illuminate\Validation\ValidationException;

$cc = CollectionCenter::where('code', 'CC1')->firstOrFail();
$main = CollectionCenter::where('code', 'MAIN')->firstOrFail();
$test = Test::where('category', 'Pathology')->where('is_active', true)->first();
if (! $test) {
    echo "SKIP: no pathology test\n";
    exit(0);
}

$suffix = (string) time();
$lp = LaboratoryPatient::create([
    'mr_no' => 'CUST'.$suffix,
    'lab_registration_no' => '97',
    'patient_name' => 'Custody Smoke',
    'gender' => 'Male',
    'contact_no' => '03001112233',
    'age' => 40,
    'priority' => 'Routine',
    'self_referred' => true,
    'selected_tests' => [[
        'id' => $test->id,
        'name' => $test->name,
        'price' => (float) $test->price,
        'carry_out' => true,
        'status' => 'Pending',
        'sample_status' => 'collected',
        'sample_collected_at' => now()->toDateTimeString(),
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

$collector = new User([
    'organization_id' => $cc->organization_id,
    'collection_center_id' => $cc->id,
    'user_scope' => User::SCOPE_COLLECTION_CENTER,
    'name' => 'Collector Ali',
    'username' => 'collector-ali',
]);
$collector->id = User::query()->value('id') ?? 1;

$dispatcher = new User([
    'organization_id' => $cc->organization_id,
    'collection_center_id' => $cc->id,
    'user_scope' => User::SCOPE_COLLECTION_CENTER,
    'name' => 'Dispatcher Sara',
    'username' => 'dispatcher-sara',
]);
$dispatcher->id = $collector->id;

$receiver = new User([
    'organization_id' => $main->organization_id,
    'collection_center_id' => null,
    'user_scope' => User::SCOPE_MAIN_LAB,
    'name' => 'Receiver Bilal',
    'username' => 'receiver-bilal',
]);
$receiver->id = $collector->id;

app(LimsBookingSync::class)->syncFromLaboratoryPatient($lp, $collector);

$vial = LabSampleVial::create([
    'laboratory_patient_id' => $lp->id,
    'barcode' => 'CUST'.$suffix,
    'vial_type' => 'EDTA',
    'vial_number' => 1,
    'test_ids' => [$test->id],
    'collected_at' => now(),
    'status' => LabSampleVial::STATUS_COLLECTED,
]);

$sample = app(LimsSampleSync::class)->syncFromVial($vial, $collector);
echo "Collect: collected_by={$sample->collected_by} name={$sample->collected_by_name}\n";

$transit = app(SampleTransitService::class);
$batch = $transit->createOpenBatch($dispatcher, [
    'destination_site_id' => $main->id,
    'create_idempotency_key' => 'custody-create-'.$suffix,
]);
$transit->addItem($batch, $sample->id, null, $dispatcher);

try {
    $transit->dispatch($batch, ['idempotency_key' => 'custody-bad-'.$suffix], $dispatcher);
    echo "FAIL: empty courier allowed\n";
    exit(1);
} catch (ValidationException $e) {
    echo 'OK: courier required — '.($e->errors()['courier_name'][0] ?? 'err')."\n";
}

$batch = $transit->dispatch($batch, [
    'courier_name' => 'City Runner',
    'courier_ref' => 'CR-CUST',
    'idempotency_key' => 'custody-dispatch-'.$suffix,
], $dispatcher);
echo "Dispatch: by={$batch->dispatched_by_name} courier={$batch->courier_name}\n";

$batch = $transit->markInTransit($batch, [
    'idempotency_key' => 'custody-transit-'.$suffix,
    'location_label' => 'Highway',
], $dispatcher);

$batch = $transit->receive($batch, [
    'idempotency_key' => 'custody-recv-'.$suffix,
    'items' => [
        ['sample_id' => $sample->id, 'receive_status' => 'received'],
    ],
], $receiver);

$sample->refresh();
$item = LimsSampleBatchItem::where('batch_id', $batch->id)->where('sample_id', $sample->id)->first();
$events = LimsTransitEvent::where('batch_id', $batch->id)->orderBy('id')->get();

echo "Receive: batch_by={$batch->received_by_name} sample_by={$sample->received_by_name} item_by={$item->receive_marked_by_name}\n";
foreach ($events as $event) {
    echo "Event {$event->event_type} actor={$event->actor_name}\n";
}

$ok = $sample->collected_by_name === 'Collector Ali'
    && $batch->dispatched_by_name === 'Dispatcher Sara'
    && $batch->courier_name === 'City Runner'
    && $batch->received_by_name === 'Receiver Bilal'
    && $sample->received_by_name === 'Receiver Bilal'
    && $item->receive_marked_by_name === 'Receiver Bilal'
    && $events->every(fn ($e) => filled($e->actor_name));

echo $ok ? "OK custody stamps\n" : "FAIL custody stamps\n";
exit($ok ? 0 : 1);
