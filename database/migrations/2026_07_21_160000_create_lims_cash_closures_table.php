<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Historical Phase 4 create — feature CANCELLED.
 * Kept so DBs that already ran it stay consistent; cleanup is
 * 2026_07_21_170000_drop_lims_cash_closures_table. Do not extend.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("DO $$ BEGIN
            CREATE TYPE cash_closure_status AS ENUM (
                'open', 'submitted', 'approved', 'rejected', 'locked'
            );
        EXCEPTION WHEN duplicate_object THEN NULL;
        END $$;");

        Schema::create('lims_cash_closures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations');
            $table->foreignId('collection_center_id')->constrained('collection_centers');
            $table->date('business_date');
            $table->text('shift_label')->default('day');
            $table->decimal('opening_float', 12, 2)->default(0);
            $table->decimal('system_cash_total', 12, 2)->default(0);
            $table->decimal('system_card_total', 12, 2)->default(0);
            $table->decimal('system_other_total', 12, 2)->default(0);
            $table->decimal('counted_cash_total', 12, 2)->nullable();
            $table->decimal('variance_cash', 12, 2)->nullable();
            $table->decimal('due_total', 12, 2)->default(0);
            $table->integer('booking_count')->default(0);
            $table->integer('payment_count')->default(0);
            $table->jsonb('summary_json')->default('{}');
            $table->foreignId('opened_by')->nullable()->constrained('users');
            $table->timestampTz('submitted_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users');
            $table->timestampTz('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->text('rejection_reason')->nullable();
            $table->text('idempotency_key')->nullable();
            $table->timestampsTz();

            $table->unique(
                ['collection_center_id', 'business_date', 'shift_label'],
                'lims_cash_closures_cc_date_shift_uq'
            );
            $table->unique(
                ['collection_center_id', 'idempotency_key'],
                'lims_cash_closures_idem_uq'
            );
            $table->index(
                ['collection_center_id', 'business_date'],
                'lims_cash_closures_cc_date_idx'
            );
        });

        DB::statement(
            "ALTER TABLE lims_cash_closures ADD COLUMN status cash_closure_status NOT NULL DEFAULT 'open'"
        );

        // Soft constraint: at most one open drawer per collection center.
        DB::statement('
            CREATE UNIQUE INDEX lims_cash_closures_one_open_uq
            ON lims_cash_closures (collection_center_id)
            WHERE status = \'open\'
        ');

        // P1 left cash_closure_id nullable without FK — wire it now.
        DB::statement('
            ALTER TABLE lims_payments
            ADD CONSTRAINT lims_payments_cash_closure_fk
            FOREIGN KEY (cash_closure_id) REFERENCES lims_cash_closures(id)
        ');

        DB::statement('
            CREATE INDEX lims_payments_cash_closure_idx
            ON lims_payments (cash_closure_id)
            WHERE cash_closure_id IS NOT NULL AND deleted_at IS NULL
        ');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS lims_payments_cash_closure_idx');
        DB::statement('ALTER TABLE lims_payments DROP CONSTRAINT IF EXISTS lims_payments_cash_closure_fk');
        Schema::dropIfExists('lims_cash_closures');
        DB::statement('DROP TYPE IF EXISTS cash_closure_status');
    }
};
