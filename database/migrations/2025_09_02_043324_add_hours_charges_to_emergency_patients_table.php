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
        Schema::table('emergency_patients', function (Blueprint $table) {
            $table->decimal('first_hour_charges', 10, 2)->nullable()->after('emergency_hours');
            $table->decimal('other_hours_charges', 10, 2)->nullable()->after('first_hour_charges');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emergency_patients', function (Blueprint $table) {
            $table->dropColumn(['first_hour_charges', 'other_hours_charges']);
        });
    }
};