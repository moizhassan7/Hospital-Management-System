<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rec = App\Models\User::where('email', 'receptionist@hospital.com')->first();
$acc = App\Models\User::where('email', 'accountant@hospital.com')->first();

echo 'Receptionist: ' . ($rec?->name ?? 'MISSING') . ' | role=' . ($rec?->roles->pluck('name')->implode(',') ?? '-') . PHP_EOL;
echo 'Accountant: ' . ($acc?->name ?? 'MISSING') . ' | role=' . ($acc?->roles->pluck('name')->implode(',') ?? '-') . PHP_EOL;
echo 'Rec login ok: ' . (Illuminate\Support\Facades\Hash::check('reception123', $rec->password) ? 'yes' : 'no') . PHP_EOL;
echo 'Acc login ok: ' . (Illuminate\Support\Facades\Hash::check('account123', $acc->password) ? 'yes' : 'no') . PHP_EOL;
echo 'Rec Create Cash Handover: ' . ($rec->hasPermission('Create Cash Handover') ? 'yes' : 'no') . PHP_EOL;
echo 'Acc Manage Doctor Payments: ' . ($acc->hasPermission('Manage Doctor Payments') ? 'yes' : 'no') . PHP_EOL;
echo 'Acc Receive Cash Handover: ' . ($acc->hasPermission('Receive Cash Handover') ? 'yes' : 'no') . PHP_EOL;

$ledger = app(App\Services\FinancialLedgerService::class)->ledger([
    'from_date' => '2020-01-01',
    'to_date' => date('Y-m-d'),
    'include_handovers' => false,
]);
echo 'Hospital ledger entries: ' . count($ledger['entries']) . PHP_EOL;
echo 'Credit: ' . $ledger['total_credit'] . ' Debit: ' . $ledger['total_debit'] . ' Net: ' . $ledger['net'] . PHP_EOL;
echo 'OK' . PHP_EOL;
