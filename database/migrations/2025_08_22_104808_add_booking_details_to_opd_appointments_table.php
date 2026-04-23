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
        Schema::table('opd_appointments', function (Blueprint $table) {
            $table->date('booking_date')->nullable();
            $table->string('status')->default('booked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opd_appointments', function (Blueprint $table) {
            $table->dropColumn('booking_date');
            $table->dropColumn('status');
        });
    }
};








