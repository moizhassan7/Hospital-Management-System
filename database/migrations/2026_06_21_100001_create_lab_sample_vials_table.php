<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_sample_vials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_patient_id')->constrained('laboratory_patients')->cascadeOnDelete();
            $table->string('barcode')->unique();
            $table->string('vial_type');
            $table->unsignedTinyInteger('vial_number')->default(1);
            $table->json('test_ids');
            $table->timestamp('collected_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status')->default('printed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_sample_vials');
    }
};
