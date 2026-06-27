<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_sample_vials', function (Blueprint $table) {
            $table->timestamp('received_in_lab_at')->nullable()->after('collected_at');
            $table->timestamp('reported_at')->nullable()->after('received_in_lab_at');
        });
    }

    public function down(): void
    {
        Schema::table('lab_sample_vials', function (Blueprint $table) {
            $table->dropColumn(['received_in_lab_at', 'reported_at']);
        });
    }
};
