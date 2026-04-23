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
        Schema::table('doctors', function (Blueprint $table) {
            $table->decimal('general_normal_fee', 8, 2)->default(0.00)->after('fee');
            $table->decimal('general_emergency_fee', 8, 2)->default(0.00)->after('general_normal_fee');
            $table->decimal('welfare_normal_fee', 8, 2)->default(0.00)->after('general_emergency_fee');
            $table->decimal('welfare_emergency_fee', 8, 2)->default(0.00)->after('welfare_normal_fee');
            $table->decimal('general_normal_percentage', 5, 2)->default(0.00)->after('welfare_emergency_fee');
            $table->decimal('general_emergency_percentage', 5, 2)->default(0.00)->after('general_normal_percentage');
            $table->decimal('welfare_normal_percentage', 5, 2)->default(0.00)->after('general_emergency_percentage');
            $table->decimal('welfare_emergency_percentage', 5, 2)->default(0.00)->after('welfare_normal_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn([
                'general_normal_fee',
                'general_emergency_fee',
                'welfare_normal_fee',
                'welfare_emergency_fee',
                'general_normal_percentage',
                'general_emergency_percentage',
                'welfare_normal_percentage',
                'welfare_emergency_percentage',
            ]);
        });
    }
};