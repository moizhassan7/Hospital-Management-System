<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laboratory_patients', function (Blueprint $table) {
            $table->id();

            // Patient Details
            $table->string('mr_no')->nullable();
            $table->string('patient_name');
            $table->string('gender');
            $table->string('contact_no')->nullable();
            $table->integer('age');
            $table->string('file_no')->nullable();
            $table->string('priority');
            $table->boolean('self_referred')->default(false);
            $table->string('refer_by_doctor_name')->nullable();

            // Test Details (JSON to store multiple tests)
            // This column will store the array of selected tests as a JSON string.
            $table->json('selected_tests');

            // Billing Summary
            $table->decimal('sub_total', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2);
            $table->decimal('lab_share_total', 10, 2);
            $table->decimal('hospital_share_total', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('due_amount', 10, 2)->default(0);
            $table->decimal('previous_due', 10, 2)->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('laboratory_patients');
    }
};