<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->unsignedBigInteger('external_id')->nullable()->unique()->after('id');
            $table->string('test_code', 32)->nullable()->after('test_id');
            $table->index('test_code', 'idx_tests_test_code');
        });

        Schema::table('test_particulars', function (Blueprint $table) {
            $table->unsignedBigInteger('external_id')->nullable()->after('id');
            $table->string('patient_type', 32)->nullable()->after('name');
            $table->string('interpretation_name')->nullable()->after('reference_text');
            $table->index(['test_id', 'patient_type'], 'idx_test_particulars_test_patient_type');
            $table->index('external_id', 'idx_test_particulars_external_id');
        });
    }

    public function down(): void
    {
        Schema::table('test_particulars', function (Blueprint $table) {
            $table->dropIndex('idx_test_particulars_test_patient_type');
            $table->dropIndex('idx_test_particulars_external_id');
            $table->dropColumn(['external_id', 'patient_type', 'interpretation_name']);
        });

        Schema::table('tests', function (Blueprint $table) {
            $table->dropIndex('idx_tests_test_code');
            $table->dropColumn(['external_id', 'test_code']);
        });
    }
};
