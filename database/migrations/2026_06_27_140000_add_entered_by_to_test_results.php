<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('test_results', 'entered_by_user_id')) {
            Schema::table('test_results', function (Blueprint $table) {
                $table->foreignId('entered_by_user_id')->nullable()->after('result_value')->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('test_results', 'entered_by_user_id')) {
            Schema::table('test_results', function (Blueprint $table) {
                $table->dropConstrainedForeignId('entered_by_user_id');
            });
        }
    }
};
