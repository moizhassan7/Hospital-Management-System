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
        Schema::table('test_particulars', function (Blueprint $table) {
            $table->string('normal_range_min')->nullable()->change();
            $table->string('normal_range_max')->nullable()->change();
            $table->string('critical_range_min')->nullable()->change();
            $table->string('critical_range_max')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_particulars', function (Blueprint $table) {
            $table->decimal('normal_range_min', 8, 2)->nullable()->change();
            $table->decimal('normal_range_max', 8, 2)->nullable()->change();
            $table->decimal('critical_range_min', 8, 2)->nullable()->change();
            $table->decimal('critical_range_max', 8, 2)->nullable()->change();
        });
    }
};
