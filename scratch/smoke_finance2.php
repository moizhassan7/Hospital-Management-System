<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Clean leftover smoke vouchers
App\Models\DoctorPaymentVoucher::where('remarks', 'smoke test')->delete();
App\Models\CashHandoverVoucher::where('remarks', 'smoke test')->delete();

$user = App\Models\User::where('username', 'admin')->first();
$doc = App\Models\Doctor::where('employee_group', 'Doctor')->first();
$svc = app(App\Services\DoctorShareService::class);
$bal = $svc->balanceSummary($doc->id);
echo "Before: " . json_encode($bal) . PHP_EOL;

$pay = min(200, $bal['pending']);
if ($pay <= 0) {
    echo "Nothing to pay\n";
    exit(0);
}

$v = App\Models\DoctorPaymentVoucher::create([
    'voucher_no' => App\Models\DoctorPaymentVoucher::generateVoucherNo(),
    'doctor_id' => $doc->id,
    'voucher_date' => now()->toDateString(),
    'total_share' => $bal['total_share'],
    'previously_paid' => $bal['total_paid'],
    'amount_paid' => $pay,
    'balance' => round($bal['pending'] - $pay, 2),
    'payment_method' => 'cash',
    'remarks' => 'smoke test',
    'created_by' => $user->id,
]);

echo "Paid {$pay}, voucher {$v->voucher_no}, balance_after={$v->balance}" . PHP_EOL;
$bal2 = $svc->balanceSummary($doc->id);
echo "After: " . json_encode($bal2) . PHP_EOL;

// Partial + pending check
assert(abs($bal2['total_paid'] - ($bal['total_paid'] + $pay)) < 0.01);
assert(abs($bal2['pending'] - ($bal['pending'] - $pay)) < 0.01);
assert(abs($v->balance - $bal2['pending']) < 0.01);

$v->delete();
echo "Partial payment flow OK" . PHP_EOL;

// Cash handover receive flow
$cash = app(App\Services\CashCollectionService::class);
$before = $cash->cashInHand($user->id);
$chv = App\Models\CashHandoverVoucher::create([
    'voucher_no' => App\Models\CashHandoverVoucher::generateVoucherNo(),
    'user_id' => $user->id,
    'voucher_date' => now()->toDateString(),
    'total_collected' => $cash->collectionsForUser($user->id)['total'],
    'previously_handed_over' => $cash->handedOver($user->id),
    'amount' => 500,
    'breakdown' => $cash->collectionsForUser($user->id)['modules'],
    'status' => 'pending',
    'remarks' => 'smoke test',
]);
$mid = $cash->cashInHand($user->id);
echo "Cash before={$before} after_pending={$mid}" . PHP_EOL;
assert(abs($before - $mid - 500) < 0.01);

$chv->update([
    'status' => 'received',
    'received_amount' => 480,
    'difference' => -20,
    'received_by' => $user->id,
    'received_at' => now(),
]);
$after = $cash->cashInHand($user->id);
echo "After receive cash_in_hand={$after} (should stay {$mid})" . PHP_EOL;
assert(abs($after - $mid) < 0.01);

$chv->delete();
echo "Cash handover receive flow OK" . PHP_EOL;
echo "ALL SMOKE TESTS PASSED" . PHP_EOL;
