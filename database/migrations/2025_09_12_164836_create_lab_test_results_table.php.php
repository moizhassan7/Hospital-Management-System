<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lab_test_results', function (Blueprint $table) {
            $table->id();
            $table->string('mr_no'); // Store patient's MR Number for easy lookup
            $table->foreignId('test_id')->constrained('tests');
            $table->json('result_details'); // Store the test results as JSON for flexibility
            $table->string('recorded_by')->nullable(); // Attendant's name or ID
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lab_test_results');
    }
};
