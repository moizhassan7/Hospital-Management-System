<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type'); // e.g., Consultant, Resident
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('speciality_id')->nullable()->constrained()->onDelete('set null');
            $table->string('room_location')->nullable();
            $table->string('employee_group')->nullable();
            $table->json('working_days')->nullable(); // Store working days as a JSON array
            $table->string('address')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('office_phone')->nullable();
            $table->string('reception_phone')->nullable();
            $table->string('accounts_of')->nullable();
            $table->string('picture')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};