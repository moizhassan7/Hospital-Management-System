<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            // Client-defined reference/normal-value tables printed on a trailing
            // report page. Stored as an array of { title, columns[], rows[][] }.
            $table->json('reference_tables')->nullable()->after('template');
        });
    }

    public function down(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->dropColumn('reference_tables');
        });
    }
};
