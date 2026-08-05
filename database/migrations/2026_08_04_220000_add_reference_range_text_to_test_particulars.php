<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_particulars', function (Blueprint $table) {
            $table->text('reference_range_text')->nullable()->after('reference_text');
        });

        // Existing imports stored min/max-in-words in reference_text.
        DB::table('test_particulars')
            ->whereNotNull('patient_type')
            ->whereNotNull('reference_text')
            ->whereNull('reference_range_text')
            ->update(['reference_range_text' => DB::raw('reference_text')]);
    }

    public function down(): void
    {
        Schema::table('test_particulars', function (Blueprint $table) {
            $table->dropColumn('reference_range_text');
        });
    }
};
