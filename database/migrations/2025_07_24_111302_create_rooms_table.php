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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID
            $table->foreignId('floor_id')->constrained()->onDelete('cascade'); // Foreign key to floors table
            $table->string('name')->unique(); // Room Name (must be unique within the hospital)
            $table->boolean('is_ward')->default(false); // Is this room a ward?
            $table->integer('number_of_beds')->nullable(); // Number of beds (if it's a ward)
            $table->decimal('per_day_rent', 8, 2)->nullable(); // Per day rent (if applicable)
            $table->timestamps(); // created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};