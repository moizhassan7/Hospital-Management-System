<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laboratory_patients', function (Blueprint $table) {
            $table->string('lab_registration_no')->nullable()->after('mr_no');
            $table->index('lab_registration_no');
        });

        if (Schema::hasColumn('laboratory_patients', 'desktop_invoice')) {
            DB::table('laboratory_patients')
                ->whereNull('lab_registration_no')
                ->whereNotNull('desktop_invoice')
                ->update([
                    'lab_registration_no' => DB::raw('desktop_invoice'),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('laboratory_patients', function (Blueprint $table) {
            $table->dropIndex(['lab_registration_no']);
            $table->dropColumn('lab_registration_no');
        });
    }
};
