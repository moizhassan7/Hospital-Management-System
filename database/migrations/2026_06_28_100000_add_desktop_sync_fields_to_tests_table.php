<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->unsignedBigInteger('desktop_test_id')->nullable()->after('test_id');
            $table->boolean('is_active')->default(true)->after('category');
            $table->string('source_hash', 64)->nullable()->after('is_active');
            $table->timestamp('source_updated_at')->nullable()->after('source_hash');

            $table->unique('desktop_test_id');
            $table->index('is_active');
        });

        // Existing synced rows: desktop PK is stored in tests.id / tests.test_id.
        DB::table('tests')
            ->where('category', 'Pathology')
            ->whereNull('desktop_test_id')
            ->update(['desktop_test_id' => DB::raw('id')]);
    }

    public function down(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->dropUnique(['desktop_test_id']);
            $table->dropIndex(['is_active']);
            $table->dropColumn(['desktop_test_id', 'is_active', 'source_hash', 'source_updated_at']);
        });
    }
};
