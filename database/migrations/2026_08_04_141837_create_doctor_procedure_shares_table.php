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
        Schema::create('doctor_procedure_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->foreignId('procedure_id')->constrained('doctors')->onDelete('cascade');
            $table->decimal('share_percentage', 5, 2)->default(0);
            $table->decimal('hospital_share', 5, 2)->default(100);
            $table->timestamps();
            
            // A doctor can only have one share configuration per procedure
            $table->unique(['doctor_id', 'procedure_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_procedure_shares');
    }
};
