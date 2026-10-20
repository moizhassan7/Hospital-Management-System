<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$out = [];

$user = App\Models\User::where('username', 'admin')->first();
$out[] = 'Admin: ' . ($user?->name ?? 'none');
$out[] = 'SuperAdmin: ' . ($user && $user->isSuperAdmin() ? 'yes' : 'no');
$out[] = 'Manage Doctor Payments: ' . ($user && $user->hasPermission('Manage Doctor Payments') ? 'yes' : 'no');
$out[] = 'Create Cash Handover: ' . ($user && $user->hasPermission('Create Cash Handover') ? 'yes' : 'no');
$out[] = 'Receive Cash Handover: ' . ($user && $user->hasPermission('Receive Cash Handover') ? 'yes' : 'no');

$doc = App\Models\Doctor::where('employee_group', 'Doctor')->first();
if ($doc) {
    $svc = app(App\Services\DoctorShareService::class);
    $bal = $svc->balanceSummary($doc->id);
    $out[] = 'Doctor: ' . $doc->name;
    $out[] = 'Balance: ' . json_encode($bal);

    if ($bal['pending'] > 0) {
        $pay = min(100, $bal['pending']);
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
        $out[] = "Created DPV {$v->voucher_no} paid={$pay} balance={$v->balance}";
        $bal2 = $svc->balanceSummary($doc->id);
        $out[] = 'After pay: ' . json_encode($bal2);
        $v->delete();
        $out[] = 'Deleted smoke DPV';
    } else {
        $out[] = 'No pending share to pay (OK for empty data)';
    }
} else {
    $out[] = 'No doctor found';
}

$cash = app(App\Services\CashCollectionService::class);
$snap = $cash->snapshot($user->id);
$out[] = 'Cash snapshot: ' . json_encode($snap);

$chv = App\Models\CashHandoverVoucher::create([
    'voucher_no' => App\Models\CashHandoverVoucher::generateVoucherNo(),
    'user_id' => $user->id,
    'voucher_date' => now()->toDateString(),
    'total_collected' => $snap['total_collected'],
    'previously_handed_over' => $snap['previously_handed_over'],
    'amount' => 1.00,
    'breakdown' => $snap['modules'],
    'status' => 'pending',
    'remarks' => 'smoke test',
]);
$out[] = 'Created CHV: ' . $chv->voucher_no;
$out[] = 'Cash after pending: ' . $cash->cashInHand($user->id);
$chv->update(['status' => 'rejected']);
$out[] = 'After reject cash: ' . $cash->cashInHand($user->id);
$chv->delete();

$out[] = 'Roles: ' . App\Models\Role::pluck('name')->implode(', ');
$out[] = 'Finance perms: ' . App\Models\Permission::where('group_name', 'Finance')->pluck('name')->implode(', ');
$out[] = 'OK';

file_put_contents(__DIR__ . '/smoke_test.txt', implode(PHP_EOL, $out));
echo implode(PHP_EOL, $out) . PHP_EOL;
