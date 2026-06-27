<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laboratory_patients', function (Blueprint $table) {
            $table->string('desktop_invoice')->nullable()->after('refer_by_doctor_name');
            $table->index(['mr_no', 'desktop_invoice']);
        });
    }

    public function down(): void
    {
        Schema::table('laboratory_patients', function (Blueprint $table) {
            $table->dropIndex(['mr_no', 'desktop_invoice']);
            $table->dropColumn('desktop_invoice');
        });
    }
};
