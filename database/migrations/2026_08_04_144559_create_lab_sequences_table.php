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
        Schema::create('lab_sequences', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->string('center_code', 20)->nullable();
            $table->bigInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['date', 'center_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_sequences');
    }
};
