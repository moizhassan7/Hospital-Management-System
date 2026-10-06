<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedures', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // Major or Minor
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('speciality_id')->nullable()->constrained()->onDelete('set null');
            $table->string('room_location')->nullable();
            $table->string('name');
            $table->string('performed_for'); // General, Male, Female
            $table->decimal('general_normal_fee', 10, 2)->default(0);
            $table->decimal('general_emergency_fee', 10, 2)->default(0);
            $table->decimal('welfare_normal_fee', 10, 2)->default(0);
            $table->decimal('welfare_emergency_fee', 10, 2)->default(0);
            $table->decimal('general_normal_doctor_percentage', 5, 2)->default(0);
            $table->decimal('general_emergency_doctor_percentage', 5, 2)->default(0);
            $table->decimal('welfare_normal_doctor_percentage', 5, 2)->default(0);
            $table->decimal('welfare_emergency_doctor_percentage', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedures');
    }
};
