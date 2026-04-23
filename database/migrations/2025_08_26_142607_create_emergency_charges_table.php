<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_charges', function (Blueprint $table) {
            $table->id();
            $table->string('charge_id')->unique();
            $table->decimal('first_hour_charges', 10, 2);
            $table->decimal('other_hours_charges', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_charges');
    }
};