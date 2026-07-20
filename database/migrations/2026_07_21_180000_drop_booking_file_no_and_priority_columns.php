<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Booking no longer captures File Number or Priority.
 * (Test catalog / commission-rule "priority" columns are unrelated and kept.)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('laboratory_patients')) {
            Schema::table('laboratory_patients', function (Blueprint $table) {
                if (Schema::hasColumn('laboratory_patients', 'file_no')) {
                    $table->dropColumn('file_no');
                }
                if (Schema::hasColumn('laboratory_patients', 'priority')) {
                    $table->dropColumn('priority');
                }
            });
        }

        if (Schema::hasTable('lims_bookings') && Schema::hasColumn('lims_bookings', 'priority')) {
            // Drop CHECK constraint before column (PostgreSQL).
            DB::statement('ALTER TABLE lims_bookings DROP CONSTRAINT IF EXISTS lims_bookings_priority_chk');

            Schema::table('lims_bookings', function (Blueprint $table) {
                $table->dropColumn('priority');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('laboratory_patients')) {
            Schema::table('laboratory_patients', function (Blueprint $table) {
                if (! Schema::hasColumn('laboratory_patients', 'file_no')) {
                    $table->string('file_no')->nullable();
                }
                if (! Schema::hasColumn('laboratory_patients', 'priority')) {
                    $table->string('priority')->default('Routine');
                }
            });
        }

        if (Schema::hasTable('lims_bookings') && ! Schema::hasColumn('lims_bookings', 'priority')) {
            Schema::table('lims_bookings', function (Blueprint $table) {
                $table->text('priority')->default('Routine');
            });

            DB::statement("
                ALTER TABLE lims_bookings
                ADD CONSTRAINT lims_bookings_priority_chk
                CHECK (priority IN ('Routine', 'Urgent', 'STAT'))
            ");
        }
    }
};
