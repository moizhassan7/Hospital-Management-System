<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('mr_number')->unique();
            $table->string('patient_type')->default('outdoor'); // 'indoor' or 'outdoor'
            $table->date('registration_date');
            $table->string('name');
            $table->string('marital_status');
            $table->date('date_of_birth');
            $table->string('relation_type')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_cnic')->nullable();
            $table->integer('age');
            $table->float('weight')->nullable();
            $table->string('gender');
            $table->string('email')->nullable();
            $table->string('cnic')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};