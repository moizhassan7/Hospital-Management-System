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
        Schema::table('test_heads', function (Blueprint $table) {
            $table->string('category')->default('Pathology')->after('name'); // Pathology, Radiology
        });

        Schema::table('tests', function (Blueprint $table) {
            $table->string('category')->default('Pathology')->after('test_head_id'); // Pathology, Radiology
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_heads', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        Schema::table('tests', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
