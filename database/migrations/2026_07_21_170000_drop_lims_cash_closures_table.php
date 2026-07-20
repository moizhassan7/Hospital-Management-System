<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Removes cancelled Phase 4 cash-close feature end-to-end from the database.
 * Idempotent: safe if create migration never ran or objects are already gone.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP INDEX IF EXISTS lims_payments_cash_closure_idx');
        DB::statement('ALTER TABLE lims_payments DROP CONSTRAINT IF EXISTS lims_payments_cash_closure_fk');

        if (Schema::hasTable('lims_payments') && Schema::hasColumn('lims_payments', 'cash_closure_id')) {
            Schema::table('lims_payments', function (Blueprint $table) {
                $table->dropColumn('cash_closure_id');
            });
        }

        Schema::dropIfExists('lims_cash_closures');
        DB::statement('DROP TYPE IF EXISTS cash_closure_status');
    }

    public function down(): void
    {
        // Intentionally empty: cash close was cancelled; do not recreate.
    }
};
