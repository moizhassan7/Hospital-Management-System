<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_patient_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emergency_patient_id')->constrained('emergency_patients')->onDelete('cascade');
            $table->foreignId('emergency_service_id')->constrained('emergency_services')->onDelete('cascade');
            $table->decimal('fee', 10, 2); // To store the fee at the time of registration
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_patient_service');
    }
};