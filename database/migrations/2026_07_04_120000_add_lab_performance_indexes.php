<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance indexes for pathology lab hot paths:
 * patient lookup, date-range reports, result entry, sample portal, barcode scan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laboratory_patients', function (Blueprint $table) {
            $table->index('created_at', 'idx_lab_patients_created_at');
            $table->index('mr_no', 'idx_lab_patients_mr_no');
            $table->index('desktop_invoice', 'idx_lab_patients_desktop_invoice');
        });

        Schema::table('lab_sample_vials', function (Blueprint $table) {
            $table->index(['laboratory_patient_id', 'vial_type', 'vial_number'], 'idx_vials_patient_type_num');
            $table->index(['laboratory_patient_id', 'status'], 'idx_vials_patient_status');
            $table->index('collected_at', 'idx_vials_collected_at');
        });

        Schema::table('test_results', function (Blueprint $table) {
            $table->index(['laboratory_patient_id', 'test_id'], 'idx_test_results_patient_test');
            $table->index('created_at', 'idx_test_results_created_at');
        });

        Schema::table('tests', function (Blueprint $table) {
            $table->index(['category', 'is_active'], 'idx_tests_category_active');
            $table->index('test_head_id', 'idx_tests_test_head_id');
        });

        Schema::table('test_particulars', function (Blueprint $table) {
            $table->index(['test_id', 'sort_order'], 'idx_test_particulars_test_sort');
        });

        Schema::table('test_heads', function (Blueprint $table) {
            $table->index('category', 'idx_test_heads_category');
        });

        Schema::table('test_result_images', function (Blueprint $table) {
            $table->index(['laboratory_patient_id', 'test_id'], 'idx_test_result_images_patient_test');
        });
    }

    public function down(): void
    {
        Schema::table('test_result_images', function (Blueprint $table) {
            $table->dropIndex('idx_test_result_images_patient_test');
        });

        Schema::table('test_heads', function (Blueprint $table) {
            $table->dropIndex('idx_test_heads_category');
        });

        Schema::table('test_particulars', function (Blueprint $table) {
            $table->dropIndex('idx_test_particulars_test_sort');
        });

        Schema::table('tests', function (Blueprint $table) {
            $table->dropIndex('idx_tests_category_active');
            $table->dropIndex('idx_tests_test_head_id');
        });

        Schema::table('test_results', function (Blueprint $table) {
            $table->dropIndex('idx_test_results_patient_test');
            $table->dropIndex('idx_test_results_created_at');
        });

        Schema::table('lab_sample_vials', function (Blueprint $table) {
            $table->dropIndex('idx_vials_patient_type_num');
            $table->dropIndex('idx_vials_patient_status');
            $table->dropIndex('idx_vials_collected_at');
        });

        Schema::table('laboratory_patients', function (Blueprint $table) {
            $table->dropIndex('idx_lab_patients_created_at');
            $table->dropIndex('idx_lab_patients_mr_no');
            $table->dropIndex('idx_lab_patients_desktop_invoice');
        });
    }
};
