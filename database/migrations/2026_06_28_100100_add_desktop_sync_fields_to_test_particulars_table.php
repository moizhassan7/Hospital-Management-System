<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_particulars', function (Blueprint $table) {
            $table->unsignedBigInteger('desktop_particular_id')->nullable()->after('test_id');
            $table->boolean('is_active')->default(true)->after('sort_order');
            $table->string('source_hash', 64)->nullable()->after('is_active');
            $table->timestamp('source_updated_at')->nullable()->after('source_hash');

            $table->unique('desktop_particular_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('test_particulars', function (Blueprint $table) {
            $table->dropUnique(['desktop_particular_id']);
            $table->dropIndex(['is_active']);
            $table->dropColumn(['desktop_particular_id', 'is_active', 'source_hash', 'source_updated_at']);
        });
    }
};
