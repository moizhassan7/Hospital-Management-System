<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'opd_appointments',
            'indoor_patients',
            'emergency_patients',
            'day_care_procedures',
            'laboratory_patients',
            'patient_discharges',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'received_by')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'opd_appointments',
            'indoor_patients',
            'emergency_patients',
            'day_care_procedures',
            'laboratory_patients',
            'patient_discharges',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'received_by')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('received_by');
                });
            }
        }
    }
};
