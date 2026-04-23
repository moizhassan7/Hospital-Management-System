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
        Schema::create('indoor_patients', function (Blueprint $table) {
            $table->id();
            // Foreign key to the patients table
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->string('mr_no')->unique(); // Store MR No. for easy lookup
            $table->string('file_no')->unique();
            $table->string('slip_no')->unique();
            $table->date('registration_date');

            // Admission details
            $table->string('admission_type'); // 'ward' or 'room'
            $table->foreignId('ward_id')->nullable()->constrained('rooms');
            $table->foreignId('room_id')->nullable()->constrained('rooms');
            $table->string('bed_no')->nullable(); // Can be a number or text

            // Fee and medical details
            $table->decimal('admission_fee', 10, 2)->default(0);
            $table->decimal('advance_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->foreignId('consultant_id')->nullable()->constrained('doctors');
            $table->boolean('is_operation')->default(false);
            $table->date('operation_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indoor_patients');
    }
};