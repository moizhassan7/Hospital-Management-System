<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Chain-of-custody display stamps (survive user rename) + per-item receive marker.
 * FKs collected_by / dispatched_by / received_by / actor_user_id already exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lims_samples', function (Blueprint $table) {
            if (! Schema::hasColumn('lims_samples', 'collected_by_name')) {
                $table->text('collected_by_name')->nullable()->after('collected_by');
            }
            if (! Schema::hasColumn('lims_samples', 'received_by_name')) {
                $table->text('received_by_name')->nullable()->after('received_by');
            }
        });

        Schema::table('lims_sample_batches', function (Blueprint $table) {
            if (! Schema::hasColumn('lims_sample_batches', 'dispatched_by_name')) {
                $table->text('dispatched_by_name')->nullable()->after('dispatched_by');
            }
            if (! Schema::hasColumn('lims_sample_batches', 'received_by_name')) {
                $table->text('received_by_name')->nullable()->after('received_by');
            }
        });

        Schema::table('lims_sample_batch_items', function (Blueprint $table) {
            if (! Schema::hasColumn('lims_sample_batch_items', 'receive_marked_by')) {
                $table->foreignId('receive_marked_by')
                    ->nullable()
                    ->after('receive_note')
                    ->constrained('users')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('lims_sample_batch_items', 'receive_marked_by_name')) {
                $table->text('receive_marked_by_name')->nullable()->after('receive_marked_by');
            }
        });

        Schema::table('lims_transit_events', function (Blueprint $table) {
            if (! Schema::hasColumn('lims_transit_events', 'actor_name')) {
                $table->text('actor_name')->nullable()->after('actor_user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lims_transit_events', function (Blueprint $table) {
            if (Schema::hasColumn('lims_transit_events', 'actor_name')) {
                $table->dropColumn('actor_name');
            }
        });

        Schema::table('lims_sample_batch_items', function (Blueprint $table) {
            if (Schema::hasColumn('lims_sample_batch_items', 'receive_marked_by')) {
                $table->dropConstrainedForeignId('receive_marked_by');
            }
            if (Schema::hasColumn('lims_sample_batch_items', 'receive_marked_by_name')) {
                $table->dropColumn('receive_marked_by_name');
            }
        });

        Schema::table('lims_sample_batches', function (Blueprint $table) {
            foreach (['dispatched_by_name', 'received_by_name'] as $col) {
                if (Schema::hasColumn('lims_sample_batches', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('lims_samples', function (Blueprint $table) {
            foreach (['collected_by_name', 'received_by_name'] as $col) {
                if (Schema::hasColumn('lims_samples', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
