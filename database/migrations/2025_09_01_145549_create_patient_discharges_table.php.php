<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('patient_discharges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indoor_patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->date('discharge_date');
            $table->time('discharge_time');
            $table->string('discharge_status');
            $table->text('discharge_summary')->nullable();
            $table->text('medication_at_discharge')->nullable();
            $table->text('follow_up_instructions')->nullable();
            $table->foreignId('certifying_doctor_id')->nullable()->constrained('doctors')->onDelete('set null');
            $table->string('payment_clearance_status');
            $table->text('cause')->nullable();
            $table->boolean('is_anesthesia')->default(false);
            $table->string('anesthesia_type')->nullable();
            $table->json('diagnoses')->nullable();
            $table->json('consultants')->nullable();
            $table->decimal('total_bill', 10, 2);
            $table->decimal('admission_fee', 10, 2);
            $table->decimal('advance_fee', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('amount_receivable', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('current_remaining', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_discharges');
    }
};