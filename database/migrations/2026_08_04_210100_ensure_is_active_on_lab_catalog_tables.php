<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tests', 'is_active')) {
            Schema::table('tests', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('report_time');
            });
        }

        if (! Schema::hasColumn('test_particulars', 'is_active')) {
            Schema::table('test_particulars', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('sort_order');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tests', 'is_active')) {
            Schema::table('tests', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }

        if (Schema::hasColumn('test_particulars', 'is_active')) {
            Schema::table('test_particulars', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
