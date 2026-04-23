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
        Schema::create('inpatient_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indoor_patient_id')->constrained('indoor_patients');
            $table->foreignId('patient_id')->constrained('patients');
            $table->json('consultant_visits')->nullable();
            $table->json('payment_history')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inpatient_details');
    }
};
