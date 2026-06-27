<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lab_settings')) {
            Schema::create('lab_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('lab_report_doctors')) {
            Schema::create('lab_report_doctors', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('designation')->nullable();
                $table->string('qualifications')->nullable();
                $table->string('phone')->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_report_doctors');
        Schema::dropIfExists('lab_settings');
    }
};
