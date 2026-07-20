<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // sample_status already created in Phase 1 bookings migration.
        DB::statement("DO $$ BEGIN
            CREATE TYPE batch_status AS ENUM (
                'open', 'dispatched', 'in_transit', 'received', 'closed', 'cancelled'
            );
        EXCEPTION WHEN duplicate_object THEN NULL;
        END $$;");

        DB::statement("DO $$ BEGIN
            CREATE TYPE transit_event_type AS ENUM (
                'created', 'dispatched', 'scanned_in_transit',
                'received', 'rejected_item', 'reopened', 'closed'
            );
        EXCEPTION WHEN duplicate_object THEN NULL;
        END $$;");

        Schema::create('lims_manifest_sequences', function (Blueprint $table) {
            $table->foreignId('collection_center_id')
                ->constrained('collection_centers');
            $table->char('business_date', 8); // YYYYMMDD Asia/Karachi
            $table->integer('next_seq')->default(1);
            $table->primary(['collection_center_id', 'business_date']);
        });

        Schema::create('lims_samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations');
            $table->foreignId('collection_center_id')->constrained('collection_centers');
            $table->foreignId('booking_id')->constrained('lims_bookings');
            $table->text('barcode');
            $table->text('vial_type')->nullable();
            $table->integer('vial_number')->nullable();
            $table->timestampTz('collected_at')->nullable();
            $table->foreignId('collected_by')->nullable()->constrained('users');
            $table->timestampTz('received_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users');
            $table->timestampTz('rejected_at')->nullable();
            $table->text('reject_reason')->nullable();
            $table->timestampTz('expires_at')->nullable();
            // Bridge to legacy Sample Portal vials (dual-write)
            $table->foreignId('lab_sample_vial_id')
                ->nullable()
                ->unique()
                ->constrained('lab_sample_vials')
                ->nullOnDelete();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['organization_id', 'barcode'], 'lims_samples_org_barcode_uq');
            $table->index('collection_center_id', 'lims_samples_cc_idx');
            $table->index('booking_id', 'lims_samples_booking_idx');
        });

        DB::statement('ALTER TABLE lims_samples ADD COLUMN status sample_status NOT NULL DEFAULT \'booked\'');

        Schema::create('lims_sample_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations');
            $table->foreignId('collection_center_id')->constrained('collection_centers');
            $table->foreignId('destination_site_id')->constrained('collection_centers');
            $table->text('manifest_no');
            $table->integer('sample_count')->default(0);
            $table->timestampTz('dispatched_at')->nullable();
            $table->foreignId('dispatched_by')->nullable()->constrained('users');
            $table->text('courier_name')->nullable();
            $table->text('courier_ref')->nullable();
            $table->timestampTz('in_transit_at')->nullable();
            $table->timestampTz('received_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users');
            $table->text('idempotency_key')->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['organization_id', 'manifest_no'], 'lims_sample_batches_manifest_uq');
            $table->unique(
                ['collection_center_id', 'idempotency_key'],
                'lims_sample_batches_idem_uq'
            );
            $table->index('collection_center_id', 'lims_sample_batches_cc_idx');
        });

        DB::statement('ALTER TABLE lims_sample_batches ADD COLUMN status batch_status NOT NULL DEFAULT \'open\'');

        Schema::create('lims_sample_batch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('lims_sample_batches')->cascadeOnDelete();
            $table->foreignId('sample_id')->constrained('lims_samples');
            $table->foreignId('booking_id')->constrained('lims_bookings');
            $table->timestampTz('added_at')->useCurrent();
            $table->text('receive_status')->default('pending');
            $table->text('receive_note')->nullable();

            $table->unique(['batch_id', 'sample_id'], 'lims_sample_batch_items_batch_sample_uq');
        });

        DB::statement("
            ALTER TABLE lims_sample_batch_items
            ADD CONSTRAINT lims_sample_batch_items_receive_chk
            CHECK (receive_status IN ('pending', 'received', 'missing', 'rejected'))
        ");

        // One membership in a non-terminal batch at a time (split shipments across
        // terminal batches allowed; app also enforces before insert).
        DB::statement('
            CREATE UNIQUE INDEX lims_sample_batch_items_active_sample_uq
            ON lims_sample_batch_items (sample_id)
            WHERE receive_status = \'pending\'
        ');

        Schema::create('lims_transit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('lims_sample_batches');
            $table->foreignId('collection_center_id')->constrained('collection_centers');
            $table->timestampTz('occurred_at')->useCurrent();
            $table->foreignId('actor_user_id')->nullable()->constrained('users');
            $table->text('location_label')->nullable();
            $table->jsonb('payload')->default('{}');
            $table->text('idempotency_key')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['batch_id', 'occurred_at'], 'lims_transit_events_batch_idx');
        });

        DB::statement('ALTER TABLE lims_transit_events ADD COLUMN event_type transit_event_type NOT NULL');

        // Partial unique: allow multiple NULL idempotency keys; enforce when set.
        DB::statement('
            CREATE UNIQUE INDEX lims_transit_events_idem_uq
            ON lims_transit_events (batch_id, event_type, idempotency_key)
            WHERE idempotency_key IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('lims_transit_events');
        Schema::dropIfExists('lims_sample_batch_items');
        Schema::dropIfExists('lims_sample_batches');
        Schema::dropIfExists('lims_samples');
        Schema::dropIfExists('lims_manifest_sequences');

        DB::statement('DROP TYPE IF EXISTS transit_event_type');
        DB::statement('DROP TYPE IF EXISTS batch_status');
    }
};
