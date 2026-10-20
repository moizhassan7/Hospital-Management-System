<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('ledger_entries')
            ->join('vouchers', 'vouchers.id', '=', 'ledger_entries.voucher_id')
            ->join('voucher_categories', 'voucher_categories.id', '=', 'vouchers.category_id')
            ->where('ledger_entries.source_type', 'voucher')
            ->select('ledger_entries.id', 'vouchers.voucher_type', 'vouchers.payment_method', 'voucher_categories.code as category_code')
            ->get();

        foreach ($rows as $row) {
            $type = match (true) {
                $row->category_code === 'cash-handover' => 'CV',
                $row->voucher_type === 'credit' && $row->payment_method === 'cash' => 'CR',
                $row->voucher_type === 'credit' => 'BR',
                $row->voucher_type === 'debit' && $row->payment_method === 'cash' => 'CP',
                default => 'BP',
            };
            DB::table('ledger_entries')->where('id', $row->id)->update(['document_type' => $type]);
        }
    }

    public function down(): void
    {
        DB::table('ledger_entries')->where('source_type', 'voucher')->update(['document_type' => 'GV']);
    }
};
