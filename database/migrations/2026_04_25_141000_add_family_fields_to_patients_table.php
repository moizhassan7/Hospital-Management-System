<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (!Schema::hasColumn('patients', 'family_name')) {
                $table->string('family_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('patients', 'family_relation')) {
                $table->string('family_relation')->nullable()->after('family_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['family_name', 'family_relation']);
        });
    }
};
