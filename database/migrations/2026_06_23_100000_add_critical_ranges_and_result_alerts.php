<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_particulars', function (Blueprint $table) {
            $table->decimal('critical_range_min', 8, 2)->nullable()->after('normal_range_max');
            $table->decimal('critical_range_max', 8, 2)->nullable()->after('critical_range_min');
        });

        Schema::create('pathology_result_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_particular_id')->constrained()->cascadeOnDelete();
            $table->string('result_value');
            $table->string('alert_type', 20); // abnormal | critical
            $table->string('flag', 10)->nullable(); // high | low
            $table->string('reported_doctor_name')->nullable();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['laboratory_patient_id', 'test_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pathology_result_alerts');

        Schema::table('test_particulars', function (Blueprint $table) {
            $table->dropColumn(['critical_range_min', 'critical_range_max']);
        });
    }
};
