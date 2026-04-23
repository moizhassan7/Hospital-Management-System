<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_patients', function (Blueprint $table) {
            $table->id();
            $table->string('mr_number');
            $table->string('patient_name');
            $table->string('age');
            $table->string('gender');
            $table->integer('emergency_hours');
            $table->decimal('first_hour_charges', 10, 2);
            $table->decimal('other_hours_charges', 10, 2);
            $table->decimal('total_fee', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_patients');
    }
};