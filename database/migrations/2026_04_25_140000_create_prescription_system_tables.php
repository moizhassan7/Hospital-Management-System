<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('content')->nullable();
            $table->timestamps();
        });

        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('medicine_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('medicine_group_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_group_id')->constrained()->onDelete('cascade');
            $table->foreignId('medicine_id')->constrained()->onDelete('cascade');
            $table->string('dosage_frequency')->nullable();
            $table->integer('duration_days')->nullable();
            $table->timestamps();
        });

        Schema::create('abstains', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('abstain_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('abstain_id')->constrained()->onDelete('cascade');
            $table->string('item');
            $table->string('duration')->nullable();
            $table->timestamps();
        });

        Schema::create('dosages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('medicine_dosages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->onDelete('cascade');
            $table->foreignId('dosage_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->boolean('is_draft')->default(false);
            $table->text('complaints')->nullable();
            $table->string('bp')->nullable();
            $table->integer('pulse')->nullable();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->integer('oxygen')->nullable();
            $table->json('diagnoses')->nullable();
            $table->json('reports')->nullable();
            $table->json('medicines')->nullable();
            $table->json('abstains')->nullable();
            $table->text('notes')->nullable();
            $table->date('next_visit_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('medicine_dosages');
        Schema::dropIfExists('dosages');
        Schema::dropIfExists('abstain_items');
        Schema::dropIfExists('abstains');
        Schema::dropIfExists('medicine_group_items');
        Schema::dropIfExists('medicine_groups');
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('diagnoses');
    }
};
