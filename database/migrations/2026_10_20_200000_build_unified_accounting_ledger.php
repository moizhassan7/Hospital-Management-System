<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_accounts', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('type')->constrained('financial_accounts')->nullOnDelete();
            $table->string('system_key', 80)->nullable()->unique()->after('parent_id');
            $table->string('normal_balance', 10)->nullable()->after('system_key');
            $table->boolean('is_postable')->default(true)->after('normal_balance');
        });

        $now = now();
        $groups = [
            ['code' => 'A', 'name' => 'Assets', 'type' => 'asset', 'system_key' => 'assets'],
            ['code' => 'A-CA', 'name' => 'Current Assets', 'type' => 'asset', 'system_key' => 'current_assets', 'parent' => 'A'],
            ['code' => 'L', 'name' => 'Liabilities', 'type' => 'liability', 'system_key' => 'liabilities'],
            ['code' => 'L-CL', 'name' => 'Current Liabilities', 'type' => 'liability', 'system_key' => 'current_liabilities', 'parent' => 'L'],
            ['code' => 'E', 'name' => 'Equity', 'type' => 'equity', 'system_key' => 'equity'],
            ['code' => 'I', 'name' => 'Income', 'type' => 'income', 'system_key' => 'income'],
            ['code' => 'I-PR', 'name' => 'Patient Service Revenue', 'type' => 'income', 'system_key' => 'patient_service_revenue', 'parent' => 'I'],
            ['code' => 'X', 'name' => 'Expenses', 'type' => 'expense', 'system_key' => 'expenses'],
            ['code' => 'X-OE', 'name' => 'Operating Expenses', 'type' => 'expense', 'system_key' => 'operating_expenses', 'parent' => 'X'],
        ];

        foreach ($groups as $group) {
            DB::table('financial_accounts')->updateOrInsert(
                ['code' => $group['code']],
                [
                    'name' => $group['name'],
                    'type' => $group['type'],
                    'system_key' => $group['system_key'],
                    'normal_balance' => in_array($group['type'], ['asset', 'expense'], true) ? 'debit' : 'credit',
                    'is_postable' => false,
                    'is_system' => true,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $groupIds = DB::table('financial_accounts')->pluck('id', 'code');
        foreach ($groups as $group) {
            if (isset($group['parent'])) {
                DB::table('financial_accounts')->where('code', $group['code'])->update([
                    'parent_id' => $groupIds[$group['parent']],
                ]);
            }
        }

        $existingParents = [
            '1000' => 'A-CA', '1010' => 'A-CA', '1020' => 'A-CA', '1030' => 'A-CA',
            '2000' => 'L-CL', '3000' => 'E', '4000' => 'I',
            '5000' => 'X-OE', '5010' => 'X-OE', '5020' => 'X-OE', '5030' => 'X-OE',
        ];
        foreach ($existingParents as $code => $parentCode) {
            $type = DB::table('financial_accounts')->where('code', $code)->value('type');
            DB::table('financial_accounts')->where('code', $code)->update([
                'parent_id' => $groupIds[$parentCode],
                'normal_balance' => in_array($type, ['asset', 'expense'], true) ? 'debit' : 'credit',
                'is_postable' => true,
            ]);
        }

        $accounts = [
            ['code' => '1040', 'name' => 'Patient Receivable', 'type' => 'asset', 'key' => 'patient_receivable', 'parent' => 'A-CA'],
            ['code' => '2010', 'name' => 'Supplier Payable', 'type' => 'liability', 'key' => 'supplier_payable', 'parent' => 'L-CL'],
            ['code' => '2020', 'name' => 'Patient Advance / Refund Payable', 'type' => 'liability', 'key' => 'patient_advance', 'parent' => 'L-CL'],
            ['code' => '2030', 'name' => 'Salary Payable', 'type' => 'liability', 'key' => 'salary_payable', 'parent' => 'L-CL'],
            ['code' => '3010', 'name' => 'Owner Capital', 'type' => 'equity', 'key' => 'owner_capital', 'parent' => 'E'],
            ['code' => '3020', 'name' => 'Opening Balance Equity', 'type' => 'equity', 'key' => 'opening_balance', 'parent' => 'E'],
            ['code' => '4010', 'name' => 'OPD Revenue', 'type' => 'income', 'key' => 'opd_revenue', 'parent' => 'I-PR'],
            ['code' => '4020', 'name' => 'IPD Revenue', 'type' => 'income', 'key' => 'ipd_revenue', 'parent' => 'I-PR'],
            ['code' => '4030', 'name' => 'Emergency Revenue', 'type' => 'income', 'key' => 'emergency_revenue', 'parent' => 'I-PR'],
            ['code' => '4040', 'name' => 'Day Care Revenue', 'type' => 'income', 'key' => 'day_care_revenue', 'parent' => 'I-PR'],
            ['code' => '4050', 'name' => 'Laboratory Revenue', 'type' => 'income', 'key' => 'laboratory_revenue', 'parent' => 'I-PR'],
            ['code' => '5040', 'name' => 'Doctor Professional Fee Expense', 'type' => 'expense', 'key' => 'doctor_fee_expense', 'parent' => 'X-OE'],
            ['code' => '5050', 'name' => 'Medical Supplies Expense', 'type' => 'expense', 'key' => 'medical_supplies_expense', 'parent' => 'X-OE'],
        ];
        foreach ($accounts as $account) {
            DB::table('financial_accounts')->updateOrInsert(
                ['code' => $account['code']],
                [
                    'name' => $account['name'],
                    'type' => $account['type'],
                    'parent_id' => $groupIds[$account['parent']],
                    'system_key' => $account['key'],
                    'normal_balance' => in_array($account['type'], ['asset', 'expense'], true) ? 'debit' : 'credit',
                    'is_postable' => true,
                    'is_system' => true,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        Schema::create('account_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('mapping_key', 80)->unique();
            $table->string('name');
            $table->foreignId('account_id')->constrained('financial_accounts')->restrictOnDelete();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(true);
            $table->timestamps();
        });

        $mappingCodes = [
            'hospital_cash' => ['Hospital Cash Account', '1000'],
            'default_bank' => ['Default Bank Account', '1010'],
            'cheque_clearing' => ['Cheque Clearing Account', '1020'],
            'staff_cash' => ['Staff Cash Custody Account', '1030'],
            'patient_receivable' => ['Patient Receivable Control', '1040'],
            'doctor_payable' => ['Doctor Payable Control', '2000'],
            'supplier_payable' => ['Supplier Payable Control', '2010'],
            'patient_advance' => ['Patient Advance / Refund Control', '2020'],
            'salary_payable' => ['Salary Payable Control', '2030'],
            'owner_drawings' => ['Owner Drawings', '3000'],
            'owner_capital' => ['Owner Capital', '3010'],
            'opening_balance' => ['Opening Balance Equity', '3020'],
            'other_income' => ['Other Income', '4000'],
            'opd_revenue' => ['OPD Revenue', '4010'],
            'ipd_revenue' => ['IPD Revenue', '4020'],
            'emergency_revenue' => ['Emergency Revenue', '4030'],
            'day_care_revenue' => ['Day Care Revenue', '4040'],
            'laboratory_revenue' => ['Laboratory Revenue', '4050'],
            'hospital_expense' => ['Hospital Operating Expense', '5000'],
            'salary_expense' => ['Salary Expense', '5010'],
            'patient_refund_expense' => ['Patient Refund / Adjustment', '5020'],
            'misc_expense' => ['Miscellaneous Expense', '5030'],
            'doctor_fee_expense' => ['Doctor Professional Fee Expense', '5040'],
            'medical_supplies_expense' => ['Medical Supplies Expense', '5050'],
        ];
        $accountIds = DB::table('financial_accounts')->pluck('id', 'code');
        foreach ($mappingCodes as $key => [$name, $code]) {
            DB::table('account_mappings')->insert([
                'mapping_key' => $key,
                'name' => $name,
                'account_id' => $accountIds[$code],
                'description' => 'System posting control used by hospital accounting.',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::rename('voucher_entries', 'ledger_entries');
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->foreignId('voucher_id')->nullable()->change();
            $table->string('source_type', 80)->nullable()->after('voucher_id');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->string('source_line', 80)->nullable()->after('source_id');
            $table->unsignedInteger('revision')->default(1)->after('source_line');
            $table->string('document_type', 10)->nullable()->after('revision');
            $table->date('transaction_date')->nullable()->after('document_type');
            $table->string('fiscal_year', 9)->nullable()->after('transaction_date');
            $table->string('reference_no')->nullable()->after('fiscal_year');
            $table->string('party_type', 30)->nullable()->after('reference_no');
            $table->unsignedBigInteger('party_id')->nullable()->after('party_type');
            $table->string('party_name')->nullable()->after('party_id');
            $table->foreignId('custodian_user_id')->nullable()->after('party_name')->constrained('users')->nullOnDelete();
            $table->boolean('is_reversal')->default(false)->after('narration');
            $table->foreignId('reversal_of_id')->nullable()->after('is_reversal')->constrained('ledger_entries')->nullOnDelete();
            $table->timestamp('reversed_at')->nullable()->after('reversal_of_id');
            $table->foreignId('reversed_by')->nullable()->after('reversed_at')->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->after('reversed_by')->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable()->after('created_by');
        });

        $legacyRows = DB::table('ledger_entries')
            ->join('vouchers', 'vouchers.id', '=', 'ledger_entries.voucher_id')
            ->select('ledger_entries.id', 'ledger_entries.entry_type', 'vouchers.voucher_date', 'vouchers.voucher_no', 'vouchers.party_type', 'vouchers.party_id', 'vouchers.party_name', 'vouchers.created_by', 'vouchers.posted_at')
            ->get();
        foreach ($legacyRows as $row) {
            DB::table('ledger_entries')->where('id', $row->id)->update([
                'source_type' => 'voucher',
                'source_id' => DB::table('ledger_entries')->where('id', $row->id)->value('voucher_id'),
                'source_line' => 'r1-'.$row->entry_type,
                'document_type' => 'GV',
                'transaction_date' => $row->voucher_date,
                'fiscal_year' => $this->fiscalYear((string) $row->voucher_date),
                'reference_no' => $row->voucher_no,
                'party_type' => $row->party_type,
                'party_id' => $row->party_id,
                'party_name' => $row->party_name,
                'created_by' => $row->created_by,
                'posted_at' => $row->posted_at ?? $now,
            ]);
        }

        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->unique(['source_type', 'source_id', 'source_line'], 'ledger_source_line_unique');
            $table->index(['transaction_date', 'account_id'], 'ledger_date_account_index');
            $table->index(['party_type', 'party_id'], 'ledger_party_index');
            $table->index(['custodian_user_id', 'transaction_date'], 'ledger_custodian_date_index');
        });

        DB::table('voucher_categories')->where('code', 'doctor-payment')->update([
            'name' => 'Doctor Payable Settlement',
            'description' => 'Settlement of an approved doctor payable balance.',
        ]);
        DB::table('voucher_categories')->where('code', 'owner-withdrawal')->update([
            'name' => 'Owner Drawings',
        ]);
        DB::table('voucher_categories')->where('code', 'cash-handover')->update([
            'name' => 'Cash Transfer (Contra)',
            'description' => 'Transfer of collection cash from staff custody to hospital cash custody.',
        ]);
    }

    public function down(): void
    {
        DB::table('voucher_categories')->where('code', 'doctor-payment')->update(['name' => 'Doctor Payment']);
        DB::table('voucher_categories')->where('code', 'owner-withdrawal')->update(['name' => 'Owner Withdrawal']);
        DB::table('voucher_categories')->where('code', 'cash-handover')->update(['name' => 'Cash Handover']);

        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->dropUnique('ledger_source_line_unique');
            $table->dropIndex('ledger_date_account_index');
            $table->dropIndex('ledger_party_index');
            $table->dropIndex('ledger_custodian_date_index');
            $table->dropForeign(['custodian_user_id']);
            $table->dropForeign(['reversal_of_id']);
            $table->dropForeign(['reversed_by']);
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'source_type', 'source_id', 'source_line', 'revision', 'document_type',
                'transaction_date', 'fiscal_year', 'reference_no', 'party_type', 'party_id',
                'party_name', 'custodian_user_id', 'is_reversal', 'reversal_of_id',
                'reversed_at', 'reversed_by', 'created_by', 'posted_at',
            ]);
            $table->foreignId('voucher_id')->nullable(false)->change();
        });
        Schema::rename('ledger_entries', 'voucher_entries');
        Schema::dropIfExists('account_mappings');

        Schema::table('financial_accounts', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropUnique(['system_key']);
            $table->dropColumn(['parent_id', 'system_key', 'normal_balance', 'is_postable']);
        });
    }

    private function fiscalYear(string $date): string
    {
        $year = (int) substr($date, 0, 4);
        $month = (int) substr($date, 5, 2);
        $start = $month >= 7 ? $year : $year - 1;

        return $start.'-'.substr((string) ($start + 1), -2);
    }
};
