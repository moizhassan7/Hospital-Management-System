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
            // Drop old columns no longer used
            $table->dropColumn(['first_hour_charges', 'other_hours_charges']);

            // Add new JSON column for consultants
            $table->json('consultants')->nullable()->after('emergency_hours');
            
            // Add new billing columns
            $table->decimal('amount_paid', 10, 2)->default(0)->after('total_fee');
            $table->decimal('amount_receivable', 10, 2)->default(0)->after('amount_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emergency_patients', function (Blueprint $table) {
            // Re-add old columns
            $table->decimal('first_hour_charges', 10, 2)->nullable();
            $table->decimal('other_hours_charges', 10, 2)->nullable();

            // Drop new columns
            $table->dropColumn(['consultants', 'amount_paid', 'amount_receivable']);
        });
    }
};