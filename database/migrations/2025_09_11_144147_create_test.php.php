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
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->string('test_id')->unique(); // For T001, T002 etc.
            $table->string('name');
            $table->decimal('price', 8, 2);
            $table->string('type');
            $table->foreignId('test_head_id')->constrained('test_heads'); // Foreign key to test_heads table
            $table->string('priority');
            $table->integer('report_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};