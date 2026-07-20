<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("DO $$ BEGIN
            CREATE TYPE commission_basis AS ENUM ('fixed', 'percent');
        EXCEPTION WHEN duplicate_object THEN NULL;
        END $$;");

        DB::statement("DO $$ BEGIN
            CREATE TYPE ledger_entry_type AS ENUM ('credit', 'debit', 'clawback', 'adjustment');
        EXCEPTION WHEN duplicate_object THEN NULL;
        END $$;");

        DB::statement("DO $$ BEGIN
            CREATE TYPE ledger_ref_type AS ENUM (
                'booking', 'invoice', 'payout', 'cancellation', 'manual'
            );
        EXCEPTION WHEN duplicate_object THEN NULL;
        END $$;");

        // Referring doctors for LIMS commissions (separate from HMS clinical `doctors`).
        Schema::create('lims_doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations');
            $table->text('code')->nullable();
            $table->text('name');
            $table->text('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['organization_id', 'code'], 'lims_doctors_org_code_uq');
            $table->index(['organization_id', 'name'], 'lims_doctors_org_name_idx');
        });

        Schema::create('lims_test_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations');
            $table->text('code');
            $table->text('name');
            $table->timestampsTz();

            $table->unique(['organization_id', 'code'], 'lims_test_categories_org_code_uq');
        });

        // Optional link from legacy tests.category string → lims_test_categories
        Schema::table('tests', function (Blueprint $table) {
            $table->foreignId('test_category_id')
                ->nullable()
                ->after('category')
                ->constrained('lims_test_categories')
                ->nullOnDelete();
        });

        Schema::create('lims_commission_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations');
            $table->foreignId('doctor_id')->nullable()->constrained('lims_doctors');
            $table->foreignId('test_category_id')->nullable()->constrained('lims_test_categories');
            $table->foreignId('test_id')->nullable()->constrained('tests');
            $table->foreignId('collection_center_id')->nullable()->constrained('collection_centers');
            $table->decimal('amount', 12, 2)->nullable();
            $table->decimal('percent', 7, 4)->nullable();
            $table->integer('priority')->default(100);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        DB::statement('ALTER TABLE lims_commission_rules ADD COLUMN basis commission_basis NOT NULL');

        DB::statement('
            ALTER TABLE lims_commission_rules
            ADD CONSTRAINT lims_commission_rules_basis_chk CHECK (
                (basis = \'fixed\' AND amount IS NOT NULL AND percent IS NULL)
                OR (basis = \'percent\' AND percent IS NOT NULL AND amount IS NULL)
            )
        ');

        DB::statement('
            ALTER TABLE lims_commission_rules
            ADD CONSTRAINT lims_commission_rules_dates_chk CHECK (
                effective_to IS NULL OR effective_to >= effective_from
            )
        ');

        DB::statement('
            CREATE INDEX lims_commission_rules_lookup_idx
            ON lims_commission_rules (
                organization_id, doctor_id, test_category_id, test_id,
                collection_center_id, priority
            )
            WHERE is_active AND deleted_at IS NULL
        ');

        Schema::create('lims_commission_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations');
            $table->foreignId('collection_center_id')->constrained('collection_centers');
            $table->foreignId('booking_id')->constrained('lims_bookings');
            $table->foreignId('booking_item_id')->constrained('lims_booking_items');
            $table->foreignId('invoice_id')->nullable()->constrained('lims_invoices');
            $table->foreignId('doctor_id')->constrained('lims_doctors');
            $table->foreignId('commission_rule_id')->nullable()->constrained('lims_commission_rules');
            $table->decimal('rule_amount', 12, 2)->nullable();
            $table->decimal('rule_percent', 7, 4)->nullable();
            $table->decimal('base_amount', 12, 2);
            $table->decimal('commission_amount', 12, 2);
            $table->char('currency', 3)->default('PKR');
            $table->timestampTz('snapshotted_at')->useCurrent();
            $table->foreignId('snapshotted_by')->nullable()->constrained('users');
            $table->boolean('is_clawed_back')->default(false);
            $table->timestampTz('clawed_back_at')->nullable();

            $table->unique('booking_item_id', 'lims_commission_snapshots_item_uq');
            $table->index(['doctor_id', 'snapshotted_at'], 'lims_commission_snapshots_doctor_idx');
            $table->index(['collection_center_id', 'snapshotted_at'], 'lims_commission_snapshots_cc_idx');
        });

        DB::statement(
            'ALTER TABLE lims_commission_snapshots ADD COLUMN rule_basis commission_basis NOT NULL'
        );

        Schema::create('lims_doctor_ledgers', function (Blueprint $table) {
            $table->foreignId('doctor_id')->primary()->constrained('lims_doctors');
            $table->foreignId('organization_id')->constrained('organizations');
            $table->decimal('balance', 14, 2)->default(0);
            $table->timestampTz('updated_at')->useCurrent();
        });

        Schema::create('lims_doctor_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations');
            $table->foreignId('doctor_id')->constrained('lims_doctors');
            $table->decimal('amount', 12, 2);
            $table->timestampTz('paid_at')->useCurrent();
            $table->foreignId('paid_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->text('idempotency_key');
            $table->timestampTz('created_at')->useCurrent();

            $table->unique(['organization_id', 'idempotency_key'], 'lims_doctor_payouts_idem_uq');
        });

        // Reuse Phase 1 payment_method enum
        DB::statement(
            "ALTER TABLE lims_doctor_payouts ADD COLUMN method payment_method NOT NULL DEFAULT 'cash'"
        );

        DB::statement('
            ALTER TABLE lims_doctor_payouts
            ADD CONSTRAINT lims_doctor_payouts_amount_chk CHECK (amount > 0)
        ');

        Schema::create('lims_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations');
            $table->foreignId('doctor_id')->constrained('lims_doctors');
            $table->foreignId('collection_center_id')->nullable()->constrained('collection_centers');
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_after', 14, 2);
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->foreignId('commission_snapshot_id')
                ->nullable()
                ->constrained('lims_commission_snapshots');
            $table->foreignId('payout_id')
                ->nullable()
                ->constrained('lims_doctor_payouts');
            $table->text('narration')->nullable();
            $table->timestampTz('occurred_at')->useCurrent();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->text('idempotency_key');
            $table->timestampTz('created_at')->useCurrent();

            $table->unique(['doctor_id', 'idempotency_key'], 'lims_ledger_entries_idem_uq');
            $table->index(['doctor_id', 'occurred_at'], 'lims_ledger_entries_doctor_idx');
            $table->index(['collection_center_id', 'occurred_at'], 'lims_ledger_entries_cc_idx');
        });

        DB::statement('ALTER TABLE lims_ledger_entries ADD COLUMN entry_type ledger_entry_type NOT NULL');
        DB::statement('ALTER TABLE lims_ledger_entries ADD COLUMN ref_type ledger_ref_type NOT NULL');

        DB::statement('
            ALTER TABLE lims_ledger_entries
            ADD CONSTRAINT lims_ledger_entries_amount_chk CHECK (amount > 0)
        ');

        // Wire lims_bookings.doctor_id → lims_doctors (was unconstrained bigint in P1)
        DB::statement('
            ALTER TABLE lims_bookings
            ADD CONSTRAINT lims_bookings_doctor_id_fk
            FOREIGN KEY (doctor_id) REFERENCES lims_doctors(id)
        ');

        // Wire lims_booking_items.test_category_id → lims_test_categories
        DB::statement('
            ALTER TABLE lims_booking_items
            ADD CONSTRAINT lims_booking_items_test_category_id_fk
            FOREIGN KEY (test_category_id) REFERENCES lims_test_categories(id)
        ');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE lims_booking_items DROP CONSTRAINT IF EXISTS lims_booking_items_test_category_id_fk');
        DB::statement('ALTER TABLE lims_bookings DROP CONSTRAINT IF EXISTS lims_bookings_doctor_id_fk');

        Schema::dropIfExists('lims_ledger_entries');
        Schema::dropIfExists('lims_doctor_payouts');
        Schema::dropIfExists('lims_doctor_ledgers');
        Schema::dropIfExists('lims_commission_snapshots');
        Schema::dropIfExists('lims_commission_rules');

        Schema::table('tests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('test_category_id');
        });

        Schema::dropIfExists('lims_test_categories');
        Schema::dropIfExists('lims_doctors');

        DB::statement('DROP TYPE IF EXISTS ledger_ref_type');
        DB::statement('DROP TYPE IF EXISTS ledger_entry_type');
        DB::statement('DROP TYPE IF EXISTS commission_basis');
    }
};
