<?php

/**
 * Phase 4 smoke: open shift → paid booking stamps closure → summary → submit
 * → approve/lock → post-lock payment unstamped → reopen day-2.
 * Run: php scratch/smoke_lims_phase4.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CollectionCenter;
use App\Models\LaboratoryPatient;
use App\Models\LimsCashClosure;
use App\Models\LimsPayment;
use App\Models\Organization;
use App\Models\Test;
use App\Models\User;
use App\Services\Lims\CashClosureService;
use App\Services\Lims\LimsBookingSync;

$org = Organization::where('code', 'MMC')->firstOrFail();
$cc = CollectionCenter::where('code', 'CC1')->firstOrFail();

$test = Test::where('category', 'Pathology')->where('is_active', true)->first();
if (! $test) {
    echo "SKIP: no pathology test\n";
    exit(0);
}

$suffix = (string) time();
$price = round((float) $test->price, 2);
if ($price <= 0) {
    $price = 500.00;
}
$paid = round($price * 0.6, 2);
$due = round($price - $paid, 2);

$mainUser = new User([
    'organization_id' => $org->id,
    'collection_center_id' => null,
    'user_scope' => User::SCOPE_MAIN_LAB,
    'name' => 'Smoke Main P4',
    'username' => 'smoke-main-p4',
]);
$mainUser->id = User::query()->value('id') ?? 1;

$ccUser = new User([
    'organization_id' => $org->id,
    'collection_center_id' => $cc->id,
    'user_scope' => User::SCOPE_COLLECTION_CENTER,
    'name' => 'Smoke CC P4',
    'username' => 'smoke-cc-p4',
]);
$ccUser->id = $mainUser->id;

$svc = app(CashClosureService::class);

// 1) Open shift
$closure = $svc->openShift($ccUser, [
    'opening_float' => 100,
    'idempotency_key' => 'smoke-open-'.$suffix,
]);
echo "Open closure id={$closure->id} date={$closure->business_date->format('Y-m-d')} shift={$closure->shift_label} status={$closure->status}\n";

$replayOpen = $svc->openShift($ccUser, [
    'idempotency_key' => 'smoke-open-'.$suffix,
]);
$openIdempotent = (int) $replayOpen->id === (int) $closure->id;

// 2) Paid booking with due remaining → payment stamps cash_closure_id
$lp = LaboratoryPatient::create([
    'mr_no' => 'LIMS4'.$suffix,
    'lab_registration_no' => '95',
    'patient_name' => 'LIMS P4 Smoke',
    'gender' => 'Male',
    'contact_no' => '03001112233',
    'age' => 42,
    'priority' => 'Routine',
    'self_referred' => true,
    'refer_by_doctor_name' => null,
    'selected_tests' => [[
        'id' => $test->id,
        'name' => $test->name,
        'price' => $price,
        'carry_out' => true,
        'status' => 'Pending',
    ]],
    'sub_total' => $price,
    'discount' => 0,
    'grand_total' => $price,
    'paid_amount' => $paid,
    'due_amount' => $due,
    'lab_share_total' => 0,
    'hospital_share_total' => 0,
    'previous_due' => 0,
    'status' => 'Pending',
]);

$booking = app(LimsBookingSync::class)->syncFromLaboratoryPatient($lp, $ccUser);
$payment = LimsPayment::withoutGlobalScopes()
    ->where('invoice_id', $booking->invoice->id)
    ->whereNull('deleted_at')
    ->first();

$stamped = $payment && (int) $payment->cash_closure_id === (int) $closure->id;
echo "Booking id={$booking->id} payment amount=".($payment?->amount ?? 'null')
    ." stamped=".($stamped ? 'yes' : 'no')." due={$due}\n";

// 3) Summary includes dues (edge case 17 — allow close with due)
$summary = $svc->buildSummary($closure, persist: true);
echo "Summary cash={$summary['system_cash_total']} due={$summary['due_total']} payments={$summary['payment_count']}\n";

// 4) Submit with counted cash → variance
$counted = round((float) $summary['system_cash_total'] + 5, 2);
$submitted = $svc->submit($closure, $counted, $ccUser);
$expectedVar = round($counted - (float) $summary['system_cash_total'], 2);
echo "Submitted status={$submitted->status} counted={$submitted->counted_cash_total} variance={$submitted->variance_cash} (expect {$expectedVar})\n";

$submitReplay = $svc->submit($submitted, $counted, $ccUser);
$submitIdempotent = (int) $submitReplay->id === (int) $submitted->id
    && $submitReplay->status === LimsCashClosure::STATUS_SUBMITTED;

// 5) Main Lab approve → locked
$locked = $svc->approve($submitted, $mainUser);
echo "Approved status={$locked->status} approved_by={$locked->approved_by}\n";

$approveReplay = $svc->approve($locked, $mainUser);
$approveIdempotent = $approveReplay->status === LimsCashClosure::STATUS_LOCKED;

// 6) Post-lock payment must NOT stamp until next open
$lp2 = LaboratoryPatient::create([
    'mr_no' => 'LIMS4B'.$suffix,
    'lab_registration_no' => '94',
    'patient_name' => 'LIMS P4 Smoke B',
    'gender' => 'Female',
    'contact_no' => '03001112234',
    'age' => 30,
    'priority' => 'Routine',
    'self_referred' => true,
    'selected_tests' => [[
        'id' => $test->id,
        'name' => $test->name,
        'price' => $price,
        'carry_out' => true,
        'status' => 'Pending',
    ]],
    'sub_total' => $price,
    'discount' => 0,
    'grand_total' => $price,
    'paid_amount' => $price,
    'due_amount' => 0,
    'lab_share_total' => 0,
    'hospital_share_total' => 0,
    'previous_due' => 0,
    'status' => 'Pending',
]);

$booking2 = app(LimsBookingSync::class)->syncFromLaboratoryPatient($lp2, $ccUser);
$payment2 = LimsPayment::withoutGlobalScopes()
    ->where('invoice_id', $booking2->invoice->id)
    ->whereNull('deleted_at')
    ->first();
$unstamped = $payment2 && $payment2->cash_closure_id === null;
echo "Post-lock payment cash_closure_id=".($payment2?->cash_closure_id ?? 'null')." (expect null)\n";

// 7) Re-open → day-2 (or next label)
$closure2 = $svc->openShift($ccUser, [
    'idempotency_key' => 'smoke-open2-'.$suffix,
]);
echo "Reopen id={$closure2->id} shift={$closure2->shift_label} status={$closure2->status}\n";

$list = $svc->listByDate($locked->business_date->format('Y-m-d'), (int) $cc->id);
$lockedFresh = LimsCashClosure::withoutGlobalScopes()->find($closure->id);

$ok = $openIdempotent
    && $stamped
    && abs((float) $summary['system_cash_total'] - $paid) < 0.001
    && (float) $summary['due_total'] >= $due - 0.001
    && $submitted->status === LimsCashClosure::STATUS_SUBMITTED
    && abs((float) $submitted->variance_cash - $expectedVar) < 0.001
    && $submitIdempotent
    && $lockedFresh?->status === LimsCashClosure::STATUS_LOCKED
    && $approveIdempotent
    && $unstamped
    && (int) $closure2->id !== (int) $closure->id
    && $closure2->status === LimsCashClosure::STATUS_OPEN
    && $list->count() >= 2;

echo $ok ? "OK\n" : "FAIL\n";
if (! $ok) {
    echo 'diag: openIdem='.($openIdempotent ? 'y' : 'n')
        .' stamped='.($stamped ? 'y' : 'n')
        .' due='.$summary['due_total']
        .' var='.$submitted->variance_cash
        .' locked='.($lockedFresh?->status ?? 'null')
        .' unstamped='.($unstamped ? 'y' : 'n')
        .' shift2='.$closure2->shift_label
        .' list='.$list->count()
        ."\n";
}
exit($ok ? 0 : 1);
