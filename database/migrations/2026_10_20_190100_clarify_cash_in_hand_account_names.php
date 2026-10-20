<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('financial_accounts')->where('code', '1000')->update(['name' => 'Hospital Cash in Hand']);
        DB::table('financial_accounts')->where('code', '1030')->update(['name' => 'Staff Cash in Hand']);
    }

    public function down(): void
    {
        DB::table('financial_accounts')->where('code', '1000')->update(['name' => 'Cash in Hand']);
        DB::table('financial_accounts')->where('code', '1030')->update(['name' => 'Staff Collection Cash']);
    }
};
