<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opd_appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_number')->unique();
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->string('mr_number');
            $table->string('patient_name');
            $table->string('age');
            $table->string('gender');
            $table->string('doctor_code');
            $table->string('doctor_name');
            $table->decimal('doctor_fee', 10, 2);
            $table->string('referred_by')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->decimal('hospital_share', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opd_appointments');
    }
};