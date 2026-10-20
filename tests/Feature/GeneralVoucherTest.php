<?php

use App\Models\FinancialAccount;
use App\Models\Doctor;
use App\Models\LedgerEntry;
use App\Models\OpdAppointment;
use App\Models\Permission;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherCategory;
use App\Services\GeneralVoucherService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function voucherUser(string $username): User
{
    return User::create([
        'name' => ucfirst($username),
        'username' => $username,
        'email' => $username.'@example.test',
        'password' => bcrypt('password'),
        'branch' => 'Main Branch',
    ]);
}

it('posts a debit voucher with equal automatic journal entries', function () {
    $user = voucherUser('accountant-test');
    $category = VoucherCategory::where('code', 'hospital-expense')->firstOrFail();
    $cash = FinancialAccount::where('code', '1000')->firstOrFail();

    $voucher = app(GeneralVoucherService::class)->create([
        'voucher_date' => '2026-10-20',
        'voucher_type' => 'debit',
        'category_id' => $category->id,
        'amount' => 1250.50,
        'payment_method' => 'cash',
        'payment_account_id' => $cash->id,
        'party_type' => 'other',
        'party_name' => 'Utility Company',
        'status' => 'posted',
    ], $user);

    expect($voucher->voucher_no)->toStartWith('CP-20261020-')
        ->and($voucher->accounting_type)->toBe('Cash Payment')
        ->and($voucher->entries)->toHaveCount(2)
        ->and((float) $voucher->entries->where('entry_type', 'debit')->sum('amount'))->toBe(1250.50)
        ->and((float) $voucher->entries->where('entry_type', 'credit')->sum('amount'))->toBe(1250.50)
        ->and($voucher->entries->firstWhere('entry_type', 'debit')->account->code)->toBe('5000')
        ->and($voucher->entries->firstWhere('entry_type', 'credit')->account->code)->toBe('1000');
});

it('posts a credit voucher to cash and the configured income account', function () {
    $user = voucherUser('credit-test');
    $category = VoucherCategory::where('code', 'other-credit')->firstOrFail();
    $cash = FinancialAccount::where('code', '1000')->firstOrFail();

    $voucher = app(GeneralVoucherService::class)->create([
        'voucher_date' => '2026-10-20',
        'voucher_type' => 'credit',
        'category_id' => $category->id,
        'amount' => 500,
        'payment_method' => 'cash',
        'payment_account_id' => $cash->id,
        'party_name' => 'Donation Box',
        'status' => 'posted',
    ], $user);

    expect($voucher->entries->firstWhere('entry_type', 'debit')->account->code)->toBe('1000')
        ->and($voucher->entries->firstWhere('entry_type', 'credit')->account->code)->toBe('4000');
});

it('keeps cash handover pending until receipt and then posts the actual amount', function () {
    $receptionist = voucherUser('reception-test');
    $receiver = voucherUser('receiver-test');
    $category = VoucherCategory::where('code', 'cash-handover')->firstOrFail();
    $staffCash = FinancialAccount::where('code', '1030')->firstOrFail();
    $service = app(GeneralVoucherService::class);

    $voucher = $service->create([
        'voucher_date' => '2026-10-20',
        'voucher_type' => 'debit',
        'category_id' => $category->id,
        'amount' => 1000,
        'payment_method' => 'cash',
        'payment_account_id' => $staffCash->id,
        'party_type' => 'user',
        'party_id' => $receptionist->id,
        'party_name' => $receptionist->name,
        'status' => 'pending',
    ], $receptionist);

    expect($voucher->entries)->toHaveCount(0)->and($voucher->status)->toBe('pending');

    $posted = $service->receiveCashHandover($voucher, 990, $receiver, 'Ten short');

    expect($posted->status)->toBe('posted')
        ->and($posted->effective_amount)->toBe(990.0)
        ->and($posted->entries)->toHaveCount(2)
        ->and($posted->entries->firstWhere('entry_type', 'debit')->account->code)->toBe('1000')
        ->and($posted->entries->firstWhere('entry_type', 'debit')->account->name)->toBe('Hospital Cash in Hand')
        ->and($posted->entries->firstWhere('entry_type', 'credit')->account->code)->toBe('1030')
        ->and($posted->entries->firstWhere('entry_type', 'credit')->account->name)->toBe('Staff Cash in Hand')
        ->and((float) $posted->entries->sum('amount'))->toBe(1980.0);
});

it('voids rather than deleting a posted voucher', function () {
    $user = voucherUser('void-test');
    $category = VoucherCategory::where('code', 'other-debit')->firstOrFail();
    $cash = FinancialAccount::where('code', '1000')->firstOrFail();
    $service = app(GeneralVoucherService::class);
    $voucher = $service->create([
        'voucher_date' => '2026-10-20',
        'voucher_type' => 'debit',
        'category_id' => $category->id,
        'amount' => 75,
        'payment_method' => 'cash',
        'payment_account_id' => $cash->id,
        'status' => 'posted',
    ], $user);

    $service->void($voucher, $user, 'Duplicate entry');

    expect(Voucher::find($voucher->id)->status)->toBe('void')
        ->and(Voucher::find($voucher->id)->void_reason)->toBe('Duplicate entry')
        ->and(Voucher::find($voucher->id)->entries)->toHaveCount(4)
        ->and(Voucher::find($voucher->id)->entries->where('is_reversal', true))->toHaveCount(2)
        ->and((float) Voucher::find($voucher->id)->entries->where('entry_type', 'debit')->sum('amount'))
        ->toBe((float) Voucher::find($voucher->id)->entries->where('entry_type', 'credit')->sum('amount'));
});

it('renders the unified finance workspace and posts a general voucher through HTTP', function () {
    $user = voucherUser('finance-web-test');
    foreach (['View Finance', 'Create Vouchers', 'Manage Voucher Categories'] as $name) {
        $permission = Permission::create(['name' => $name, 'group_name' => 'Finance']);
        $user->permissions()->attach($permission);
    }

    $this->actingAs($user)->get(route('finance.index'))->assertOk()->assertSee('unified voucher desk');
    $this->actingAs($user)->get(route('finance.vouchers.index'))->assertOk()->assertSee('Voucher daybook');
    $this->actingAs($user)->get(route('finance.vouchers.create'))->assertOk()->assertSee('Posting preview');
    $this->actingAs($user)->get(route('finance.categories.index'))->assertOk()->assertSee('Voucher categories');

    $response = $this->actingAs($user)->post(route('finance.vouchers.store'), [
        'category' => 'hospital-expense',
        'voucher_date' => '2026-10-20',
        'amount' => '850.00',
        'payment_method' => 'bank',
        'party_type' => 'other',
        'party_name' => 'Electricity Office',
        'reference_no' => 'BILL-19',
        'remarks' => 'October electricity bill',
    ]);

    $voucher = Voucher::where('reference_no', 'BILL-19')->firstOrFail();
    $response->assertRedirect(route('finance.vouchers.show', $voucher));
    expect($voucher->status)->toBe('posted')->and($voucher->entries)->toHaveCount(2);
    $this->actingAs($user)->get(route('finance.vouchers.show', $voucher))->assertOk()->assertSee('Journal posting')->assertSee('Bank Payment');
    $this->actingAs($user)->get(route('finance.vouchers.index'))
        ->assertOk()
        ->assertSee('Journal Debit (Dr)')
        ->assertSee('Journal Credit (Cr)')
        ->assertSee('Hospital Expenses')
        ->assertSee('Bank');
});

it('posts a hospital collection once to the unified ledger', function () {
    $user = voucherUser('opd-cashier-test');
    Doctor::create([
        'code' => 'DOC-1',
        'name' => 'Doctor One',
        'type' => 'Consultant',
        'general_normal_percentage' => 25,
    ]);

    $appointment = OpdAppointment::create([
        'appointment_number' => 'AP-20261020-99',
        'appointment_date' => '2026-10-20',
        'appointment_time' => '10:00:00',
        'mr_number' => '00999',
        'patient_name' => 'Ledger Patient',
        'age' => '30',
        'gender' => 'Male',
        'doctor_code' => 'DOC-1',
        'doctor_name' => 'Doctor One',
        'doctor_fee' => 1200,
        'total_amount' => 1200,
        'hospital_share' => 600,
        'status' => 'completed',
        'received_by' => $user->id,
    ]);

    app(\App\Services\LedgerPostingService::class)->syncModel($appointment);

    $entries = LedgerEntry::where('source_type', 'opd_appointment')->where('source_id', $appointment->id)->get();
    expect($entries)->toHaveCount(2)
        ->and((float) $entries->where('entry_type', 'debit')->sum('amount'))->toBe(1200.0)
        ->and((float) $entries->where('entry_type', 'credit')->sum('amount'))->toBe(1200.0)
        ->and($entries->firstWhere('entry_type', 'debit')->account->system_key)->toBeNull()
        ->and($entries->firstWhere('entry_type', 'debit')->account->code)->toBe('1030')
        ->and($entries->firstWhere('entry_type', 'credit')->account->code)->toBe('4010');

    $accrual = LedgerEntry::where('source_type', 'doctor_share_opd')->where('source_id', $appointment->id)->get();
    expect($accrual)->toHaveCount(2)
        ->and((float) $accrual->where('entry_type', 'debit')->sum('amount'))->toBe(300.0)
        ->and($accrual->firstWhere('entry_type', 'debit')->account->code)->toBe('5040')
        ->and($accrual->firstWhere('entry_type', 'credit')->account->code)->toBe('2000');
});

it('renders the chart of accounts and a balanced trial balance', function () {
    $user = voucherUser('reports-test');
    $permission = Permission::create(['name' => 'View Finance', 'group_name' => 'Finance']);
    $user->permissions()->attach($permission);

    $this->actingAs($user)->get(route('finance.accounts.index'))
        ->assertOk()->assertSee('Chart of accounts')->assertSee('Patient Service Revenue');
    $this->actingAs($user)->get(route('finance.reports.trial_balance'))
        ->assertOk()->assertSee('Trial balance')->assertSee('Trial balance is balanced');
});
