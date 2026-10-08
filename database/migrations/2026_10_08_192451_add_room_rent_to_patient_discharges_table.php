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
        Schema::table('patient_discharges', function (Blueprint $table) {
            $table->integer('room_days')->default(0)->after('total_bill');
            $table->decimal('room_rent', 10, 2)->default(0)->after('room_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_discharges', function (Blueprint $table) {
            $table->dropColumn(['room_days', 'room_rent']);
        });
    }
};
