<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('desktop_booking_id')->unique();
            $table->string('lab_registration_no')->index();
            $table->string('mr_no')->nullable()->index();
            $table->string('patient_name');
            $table->string('gender')->default('Other');
            $table->string('contact_no')->nullable();
            $table->unsignedSmallInteger('age')->default(0);
            $table->string('file_no')->nullable();
            $table->string('refer_by_doctor_name')->nullable();
            $table->boolean('self_referred')->default(false);
            $table->date('booking_date')->nullable();
            $table->string('priority')->default('Routine');
            $table->decimal('sub_total', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);
            $table->decimal('lab_share_total', 12, 2)->default(0);
            $table->decimal('hospital_share_total', 12, 2)->default(0);
            $table->string('status')->default('Pending');
            $table->string('source_hash', 64)->nullable();
            $table->timestamp('source_updated_at')->nullable();
            $table->foreignId('laboratory_patient_id')->nullable()->constrained('laboratory_patients')->nullOnDelete();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_orders');
    }
};
