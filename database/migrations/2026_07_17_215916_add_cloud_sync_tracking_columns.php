<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('laboratory_patients', function (Blueprint $table) {
            $table->uuid('sync_id')->nullable()->unique()->after('id');
            $table->enum('sync_status', ['pending', 'synced', 'failed'])->default('pending')->after('status');
            $table->timestamp('synced_at')->nullable()->after('sync_status');
        });

        Schema::table('test_results', function (Blueprint $table) {
            $table->uuid('sync_id')->nullable()->unique()->after('id');
            $table->enum('sync_status', ['pending', 'synced', 'failed'])->default('pending')->after('value');
            $table->timestamp('synced_at')->nullable()->after('sync_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laboratory_patients', function (Blueprint $table) {
            $table->dropColumn(['sync_id', 'sync_status', 'synced_at']);
        });

        Schema::table('test_results', function (Blueprint $table) {
            $table->dropColumn(['sync_id', 'sync_status', 'synced_at']);
        });
    }
};
