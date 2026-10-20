<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('type', 20);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('voucher_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('name');
            $table->string('default_type', 10);
            $table->foreignId('account_id')->constrained('financial_accounts')->restrictOnDelete();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no')->nullable()->unique();
            $table->date('voucher_date');
            $table->string('voucher_type', 10);
            $table->foreignId('category_id')->constrained('voucher_categories')->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->decimal('actual_amount', 14, 2)->nullable();
            $table->string('payment_method', 20)->default('cash');
            $table->foreignId('payment_account_id')->nullable()->constrained('financial_accounts')->restrictOnDelete();
            $table->string('party_type', 30)->nullable();
            $table->unsignedBigInteger('party_id')->nullable();
            $table->string('party_name')->nullable();
            $table->string('reference_no')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status', 20)->default('posted');
            $table->json('metadata')->nullable();
            $table->string('source_type', 80)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->timestamps();

            $table->index(['voucher_date', 'voucher_type']);
            $table->index(['category_id', 'status']);
            $table->index(['party_type', 'party_id']);
            $table->unique(['source_type', 'source_id'], 'vouchers_source_unique');
        });

        Schema::create('voucher_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('financial_accounts')->restrictOnDelete();
            $table->string('entry_type', 10);
            $table->decimal('amount', 14, 2);
            $table->string('narration')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'entry_type']);
        });

        $now = now();
        $accounts = [
            ['code' => '1000', 'name' => 'Hospital Cash in Hand', 'type' => 'asset'],
            ['code' => '1010', 'name' => 'Bank', 'type' => 'asset'],
            ['code' => '1020', 'name' => 'Cheque Clearing', 'type' => 'asset'],
            ['code' => '1030', 'name' => 'Staff Cash in Hand', 'type' => 'asset'],
            ['code' => '2000', 'name' => 'Doctor Payable', 'type' => 'liability'],
            ['code' => '3000', 'name' => 'Owner Drawings', 'type' => 'equity'],
            ['code' => '4000', 'name' => 'Other Income', 'type' => 'income'],
            ['code' => '5000', 'name' => 'Hospital Expenses', 'type' => 'expense'],
            ['code' => '5010', 'name' => 'Salaries Expense', 'type' => 'expense'],
            ['code' => '5020', 'name' => 'Patient Refunds', 'type' => 'expense'],
            ['code' => '5030', 'name' => 'Miscellaneous Expense', 'type' => 'expense'],
        ];

        foreach ($accounts as $account) {
            DB::table('financial_accounts')->insert(array_merge($account, [
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $accountIds = DB::table('financial_accounts')->pluck('id', 'code');
        $categories = [
            ['code' => 'patient-refund', 'name' => 'Patient Refund', 'default_type' => 'debit', 'account' => '5020', 'description' => 'Return excess or refundable payment to a patient.'],
            ['code' => 'hospital-expense', 'name' => 'Hospital Expense', 'default_type' => 'debit', 'account' => '5000', 'description' => 'General hospital operating expense.'],
            ['code' => 'salary-payment', 'name' => 'Salary Payment', 'default_type' => 'debit', 'account' => '5010', 'description' => 'Salary or staff payment.'],
            ['code' => 'owner-withdrawal', 'name' => 'Owner Withdrawal', 'default_type' => 'debit', 'account' => '3000', 'description' => 'Cash or bank withdrawal by a hospital owner.'],
            ['code' => 'doctor-payment', 'name' => 'Doctor Payment', 'default_type' => 'debit', 'account' => '2000', 'description' => 'Payment against a doctor share balance.'],
            ['code' => 'cash-handover', 'name' => 'Cash Handover', 'default_type' => 'debit', 'account' => '1000', 'description' => 'Reception collection handed over to accounts.'],
            ['code' => 'other-credit', 'name' => 'Other Credit', 'default_type' => 'credit', 'account' => '4000', 'description' => 'Any other amount received by the hospital.'],
            ['code' => 'other-debit', 'name' => 'Other Debit', 'default_type' => 'debit', 'account' => '5030', 'description' => 'Any other amount paid by the hospital.'],
        ];

        foreach ($categories as $category) {
            DB::table('voucher_categories')->insert([
                'code' => $category['code'],
                'name' => $category['name'],
                'default_type' => $category['default_type'],
                'account_id' => $accountIds[$category['account']],
                'description' => $category['description'],
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $categoryIds = DB::table('voucher_categories')->pluck('id', 'code');

        if (Schema::hasTable('doctor_payment_vouchers')) {
            foreach (DB::table('doctor_payment_vouchers')->orderBy('id')->get() as $legacy) {
                $doctorName = DB::table('doctors')->where('id', $legacy->doctor_id)->value('name') ?? 'Doctor';
                $paymentCode = match ($legacy->payment_method) {
                    'bank' => '1010',
                    'cheque' => '1020',
                    default => '1000',
                };
                $voucherId = DB::table('vouchers')->insertGetId([
                    'voucher_no' => $legacy->voucher_no,
                    'voucher_date' => $legacy->voucher_date,
                    'voucher_type' => 'debit',
                    'category_id' => $categoryIds['doctor-payment'],
                    'amount' => $legacy->amount_paid,
                    'payment_method' => $legacy->payment_method,
                    'payment_account_id' => $accountIds[$paymentCode],
                    'party_type' => 'doctor',
                    'party_id' => $legacy->doctor_id,
                    'party_name' => $doctorName,
                    'reference_no' => $legacy->reference_no,
                    'remarks' => $legacy->remarks,
                    'status' => 'posted',
                    'metadata' => json_encode([
                        'total_share' => (float) $legacy->total_share,
                        'previously_paid' => (float) $legacy->previously_paid,
                        'balance' => (float) $legacy->balance,
                    ]),
                    'source_type' => 'doctor_payment_voucher',
                    'source_id' => $legacy->id,
                    'created_by' => $legacy->created_by,
                    'posted_by' => $legacy->created_by,
                    'posted_at' => $legacy->created_at,
                    'created_at' => $legacy->created_at,
                    'updated_at' => $legacy->updated_at,
                ]);

                DB::table('voucher_entries')->insert([
                    [
                        'voucher_id' => $voucherId,
                        'account_id' => $accountIds['2000'],
                        'entry_type' => 'debit',
                        'amount' => $legacy->amount_paid,
                        'narration' => 'Doctor payment - '.$doctorName,
                        'created_at' => $legacy->created_at,
                        'updated_at' => $legacy->updated_at,
                    ],
                    [
                        'voucher_id' => $voucherId,
                        'account_id' => $accountIds[$paymentCode],
                        'entry_type' => 'credit',
                        'amount' => $legacy->amount_paid,
                        'narration' => 'Doctor payment - '.$doctorName,
                        'created_at' => $legacy->created_at,
                        'updated_at' => $legacy->updated_at,
                    ],
                ]);
            }
        }

        if (Schema::hasTable('cash_handover_vouchers')) {
            foreach (DB::table('cash_handover_vouchers')->orderBy('id')->get() as $legacy) {
                $staffName = DB::table('users')->where('id', $legacy->user_id)->value('name') ?? 'Staff';
                $status = match ($legacy->status) {
                    'received' => 'posted',
                    'rejected' => 'rejected',
                    default => 'pending',
                };
                $actualAmount = $legacy->status === 'received'
                    ? ($legacy->received_amount ?? $legacy->amount)
                    : null;
                $breakdown = is_string($legacy->breakdown)
                    ? json_decode($legacy->breakdown, true)
                    : $legacy->breakdown;
                $metadata = [
                    'total_collected' => (float) $legacy->total_collected,
                    'previously_handed_over' => (float) $legacy->previously_handed_over,
                    'breakdown' => $breakdown,
                    'received_amount' => $legacy->received_amount !== null ? (float) $legacy->received_amount : null,
                    'difference' => $legacy->difference !== null ? (float) $legacy->difference : null,
                    'received_by' => $legacy->received_by,
                    'received_at' => $legacy->received_at,
                    'receiver_remarks' => $legacy->receiver_remarks,
                ];

                $voucherId = DB::table('vouchers')->insertGetId([
                    'voucher_no' => $legacy->voucher_no,
                    'voucher_date' => $legacy->voucher_date,
                    'voucher_type' => 'debit',
                    'category_id' => $categoryIds['cash-handover'],
                    'amount' => $legacy->amount,
                    'actual_amount' => $actualAmount,
                    'payment_method' => 'cash',
                    'payment_account_id' => $accountIds['1030'],
                    'party_type' => 'user',
                    'party_id' => $legacy->user_id,
                    'party_name' => $staffName,
                    'remarks' => $legacy->remarks,
                    'status' => $status,
                    'metadata' => json_encode($metadata),
                    'source_type' => 'cash_handover_voucher',
                    'source_id' => $legacy->id,
                    'created_by' => $legacy->user_id,
                    'posted_by' => $status === 'posted' ? $legacy->received_by : null,
                    'posted_at' => $status === 'posted' ? $legacy->received_at : null,
                    'created_at' => $legacy->created_at,
                    'updated_at' => $legacy->updated_at,
                ]);

                if ($status === 'posted') {
                    DB::table('voucher_entries')->insert([
                        [
                            'voucher_id' => $voucherId,
                            'account_id' => $accountIds['1000'],
                            'entry_type' => 'debit',
                            'amount' => $actualAmount,
                            'narration' => 'Cash handover - '.$staffName,
                            'created_at' => $legacy->created_at,
                            'updated_at' => $legacy->updated_at,
                        ],
                        [
                            'voucher_id' => $voucherId,
                            'account_id' => $accountIds['1030'],
                            'entry_type' => 'credit',
                            'amount' => $actualAmount,
                            'narration' => 'Cash handover - '.$staffName,
                            'created_at' => $legacy->created_at,
                            'updated_at' => $legacy->updated_at,
                        ],
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_entries');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('voucher_categories');
        Schema::dropIfExists('financial_accounts');
    }
};
