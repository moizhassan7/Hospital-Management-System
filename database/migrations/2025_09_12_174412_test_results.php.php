<?php

// database/migrations/xxxx_xx_xx_create_test_results_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('test_id')->constrained();
            $table->foreignId('test_particular_id')->constrained();
            $table->string('result_value'); // Can be numeric or text
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('test_results');
    }
};