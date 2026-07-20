<?php

/**
 * Phase 3 smoke: rule → book → snapshot+credit → rule edit (old snap frozen)
 * → cancel clawback → rebook (new rule) → payout.
 * Run: php scratch/smoke_lims_phase3.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CollectionCenter;
use App\Models\LaboratoryPatient;
use App\Models\LimsBooking;
use App\Models\LimsCommissionRule;
use App\Models\LimsCommissionSnapshot;
use App\Models\LimsDoctor;
use App\Models\LimsDoctorLedger;
use App\Models\LimsDoctorPayout;
use App\Models\LimsLedgerEntry;
use App\Models\Organization;
use App\Models\Test;
use App\Models\User;
use App\Services\Lims\CommissionSnapshotService;
use App\Services\Lims\DoctorPayoutService;
use App\Services\Lims\LimsBookingSync;

$org = Organization::where('code', 'MMC')->firstOrFail();
$cc = CollectionCenter::where('code', 'CC1')->firstOrFail();

$test = Test::where('category', 'Pathology')->where('is_active', true)->first();
if (! $test) {
    echo "SKIP: no pathology test\n";
    exit(0);
}

$suffix = (string) time();
$doctorName = 'Dr LIMS P3 Smoke '.$suffix;
$price = round((float) $test->price, 2);
if ($price <= 0) {
    $price = 1000.00;
}

$mainUser = new User([
    'organization_id' => $org->id,
    'collection_center_id' => null,
    'user_scope' => User::SCOPE_MAIN_LAB,
    'name' => 'Smoke Main P3',
    'username' => 'smoke-main-p3',
]);
$mainUser->id = User::query()->value('id') ?? 1;

$ccUser = new User([
    'organization_id' => $org->id,
    'collection_center_id' => $cc->id,
    'user_scope' => User::SCOPE_COLLECTION_CENTER,
    'name' => 'Smoke CC P3',
    'username' => 'smoke-cc-p3',
]);
$ccUser->id = $mainUser->id;

// 1) Commission rule: 10% for all doctors / this test (org default doctor_id null)
$rule = LimsCommissionRule::query()->create([
    'organization_id' => $org->id,
    'doctor_id' => null,
    'test_category_id' => null,
    'test_id' => $test->id,
    'collection_center_id' => $cc->id,
    'basis' => LimsCommissionRule::BASIS_PERCENT,
    'amount' => null,
    'percent' => 10,
    'priority' => 50,
    'effective_from' => now('Asia/Karachi')->toDateString(),
    'effective_to' => null,
    'is_active' => true,
    'created_by' => $mainUser->id,
]);
echo "Rule id={$rule->id} basis=percent 10%\n";

$expectedFirst = round($price * 0.10, 2);

// 2) Book with referrer → sync → snapshot + credit
$lp = LaboratoryPatient::create([
    'mr_no' => 'LIMS3'.$suffix,
    'lab_registration_no' => '97',
    'patient_name' => 'LIMS P3 Smoke',
    'gender' => 'Male',
    'contact_no' => '03007654321',
    'age' => 40,
    'priority' => 'Routine',
    'self_referred' => false,
    'refer_by_doctor_name' => $doctorName,
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

$booking = app(LimsBookingSync::class)->syncFromLaboratoryPatient($lp, $ccUser);
echo "Booking id={$booking->id} doctor_id={$booking->doctor_id} lab={$booking->lab_number}\n";

$doctor = LimsDoctor::query()->findOrFail($booking->doctor_id);
$snap = LimsCommissionSnapshot::withoutGlobalScopes()
    ->where('booking_id', $booking->id)
    ->first();

if (! $snap) {
    echo "FAIL: no commission snapshot\n";
    exit(1);
}

$frozenAmount = (float) $snap->commission_amount;
echo "Snapshot id={$snap->id} amount={$frozenAmount} (expect {$expectedFirst})\n";

$ledger = LimsDoctorLedger::query()->where('doctor_id', $doctor->id)->first();
$balanceAfterCredit = (float) ($ledger?->balance ?? 0);
echo "Ledger balance after credit={$balanceAfterCredit}\n";

$credit = LimsLedgerEntry::query()
    ->where('doctor_id', $doctor->id)
    ->where('idempotency_key', 'credit:snapshot:'.$snap->booking_item_id)
    ->first();

// 3) Edit rule → old snapshot must NOT change
$rule->percent = 25;
$rule->save();
$snap->refresh();
$unchanged = abs((float) $snap->commission_amount - $frozenAmount) < 0.001;
echo "After rule edit to 25%, snapshot still {$snap->commission_amount}? ".($unchanged ? 'yes' : 'no')."\n";

// 4) Cancel → clawback
$cancelled = app(CommissionSnapshotService::class)->cancelBooking($booking, $ccUser, 'smoke cancel');
$snap->refresh();
$claw = LimsLedgerEntry::query()
    ->where('doctor_id', $doctor->id)
    ->where('idempotency_key', 'clawback:snapshot:'.$snap->id)
    ->first();
$ledger->refresh();
echo "Cancelled status={$cancelled->status} clawed={$snap->is_clawed_back} balance={$ledger->balance}\n";

// 5) New booking uses edited rule (25%)
$lp2 = LaboratoryPatient::create([
    'mr_no' => 'LIMS3B'.$suffix,
    'lab_registration_no' => '96',
    'patient_name' => 'LIMS P3 Smoke B',
    'gender' => 'Female',
    'contact_no' => '03007654322',
    'age' => 35,
    'priority' => 'Routine',
    'self_referred' => false,
    'refer_by_doctor_name' => $doctorName,
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
$snap2 = LimsCommissionSnapshot::withoutGlobalScopes()
    ->where('booking_id', $booking2->id)
    ->first();
$expectedSecond = round($price * 0.25, 2);
echo "Booking2 snap amount=".($snap2?->commission_amount ?? 'null')." (expect {$expectedSecond})\n";

$ledger->refresh();
$balanceBeforePayout = (float) $ledger->balance;

// 6) Payout DEBIT (no recompute)
$payoutAmount = round(min($balanceBeforePayout, $expectedSecond) * 0.5, 2);
if ($payoutAmount <= 0) {
    $payoutAmount = round($expectedSecond * 0.5, 2);
}
$payout = app(DoctorPayoutService::class)->payout(
    $doctor,
    $payoutAmount,
    $mainUser,
    'cash',
    'smoke payout',
    'smoke-payout-'.$suffix,
);
$ledger->refresh();
echo "Payout id={$payout->id} amount={$payout->amount} balance after={$ledger->balance}\n";

$payoutReplay = app(DoctorPayoutService::class)->payout(
    $doctor,
    $payoutAmount,
    $mainUser,
    'cash',
    'smoke payout',
    'smoke-payout-'.$suffix,
);
$idempotentPayout = (int) $payoutReplay->id === (int) $payout->id;

$ok = $booking->doctor_id !== null
    && $snap !== null
    && abs($frozenAmount - $expectedFirst) < 0.001
    && $credit !== null
    && abs($balanceAfterCredit - $expectedFirst) < 0.001
    && $unchanged
    && $cancelled->status === LimsBooking::STATUS_CANCELLED
    && $snap->is_clawed_back
    && $claw !== null
    && $snap2 !== null
    && abs((float) $snap2->commission_amount - $expectedSecond) < 0.001
    && abs((float) $snap->commission_amount - $frozenAmount) < 0.001 // still frozen after everything
    && $payout instanceof LimsDoctorPayout
    && $idempotentPayout
    && abs((float) $ledger->balance - ($balanceBeforePayout - $payoutAmount)) < 0.001;

echo $ok ? "OK\n" : "FAIL\n";
if (! $ok) {
    echo "diag: frozen={$frozenAmount} expectedFirst={$expectedFirst} credit=".($credit?->id ?? 'null')
        ." claw=".($claw?->id ?? 'null')
        ." snap2=".($snap2?->commission_amount ?? 'null')
        ." payoutIdem=".($idempotentPayout ? 'yes' : 'no')."\n";
}
exit($ok ? 0 : 1);
