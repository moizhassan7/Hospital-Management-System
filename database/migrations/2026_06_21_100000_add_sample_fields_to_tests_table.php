<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->unsignedInteger('sample_expiry_hours')->nullable()->after('report_time');
            $table->string('sample_vial')->nullable()->after('sample_expiry_hours');
            $table->unsignedTinyInteger('vials_required')->default(1)->after('sample_vial');
        });
    }

    public function down(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->dropColumn(['sample_expiry_hours', 'sample_vial', 'vials_required']);
        });
    }
};
