<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_particulars', function (Blueprint $table) {
            $table->string('result_key')->nullable()->after('name');
            $table->text('remarks')->nullable()->after('reference_text');
            $table->string('formula')->nullable()->after('remarks');
            $table->boolean('is_calculated')->default(false)->after('formula');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('is_calculated');
        });
    }

    public function down(): void
    {
        Schema::table('test_particulars', function (Blueprint $table) {
            $table->dropColumn(['result_key', 'remarks', 'formula', 'is_calculated', 'sort_order']);
        });
    }
};
