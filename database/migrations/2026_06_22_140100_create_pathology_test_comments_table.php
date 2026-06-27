<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pathology_test_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_id')->constrained()->cascadeOnDelete();
            $table->longText('comment')->nullable();
            $table->timestamps();

            $table->unique(['laboratory_patient_id', 'test_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pathology_test_comments');
    }
};
