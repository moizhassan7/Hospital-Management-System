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
            CREATE TYPE booking_status AS ENUM (
                'draft', 'booked', 'partially_collected', 'collected',
                'dispatched', 'in_transit', 'received', 'processing',
                'partially_reported', 'reported', 'cancelled'
            );
        EXCEPTION WHEN duplicate_object THEN NULL;
        END $$;");

        DB::statement("DO $$ BEGIN
            CREATE TYPE sample_status AS ENUM (
                'booked', 'collected', 'dispatched', 'in_transit',
                'received', 'rejected', 'expired', 'processing', 'completed'
            );
        EXCEPTION WHEN duplicate_object THEN NULL;
        END $$;");

        Schema::create('lab_number_sequences', function (Blueprint $table) {
            $table->foreignId('collection_center_id')
                ->constrained('collection_centers');
            $table->char('year_month', 6);
            $table->integer('next_seq')->default(1);
            $table->primary(['collection_center_id', 'year_month']);
        });

        Schema::create('lims_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations');
            $table->foreignId('collection_center_id')->constrained('collection_centers');
            $table->foreignId('patient_id')->constrained('lims_patients');
            // doctors table is P3 — nullable until then; name kept for dual-write
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->text('refer_by_doctor_name')->nullable();
            $table->boolean('self_referred')->default(false);
            $table->text('lab_number');
            $table->char('lab_number_year_month', 6);
            $table->integer('lab_number_seq');
            $table->text('priority')->default('Routine');
            $table->timestampTz('booked_at')->useCurrent();
            $table->foreignId('booked_by')->nullable()->constrained('users');
            $table->timestampTz('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            $table->text('cancel_reason')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('sync_id')->unique();
            // Bridge to legacy pathology booking row (dual-write)
            $table->foreignId('laboratory_patient_id')
                ->nullable()
                ->unique()
                ->constrained('laboratory_patients')
                ->nullOnDelete();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['organization_id', 'lab_number'], 'lims_bookings_lab_number_uq');
            $table->unique(
                ['collection_center_id', 'lab_number_year_month', 'lab_number_seq'],
                'lims_bookings_cc_ym_seq_uq'
            );
        });

        DB::statement('ALTER TABLE lims_bookings ADD COLUMN status booking_status NOT NULL DEFAULT \'booked\'');

        DB::statement("
            ALTER TABLE lims_bookings
            ADD CONSTRAINT lims_bookings_priority_chk
            CHECK (priority IN ('Routine', 'Urgent', 'STAT'))
        ");

        // Relaxed vs architecture: doctor_id optional until P3 doctors exist.
        // Non-self-referred rows may carry refer_by_doctor_name only.
        DB::statement("
            ALTER TABLE lims_bookings
            ADD CONSTRAINT lims_bookings_self_ref_chk CHECK (
                (self_referred = true AND doctor_id IS NULL)
                OR (self_referred = false)
            )
        ");

        DB::statement('
            CREATE INDEX lims_bookings_cc_booked_idx
            ON lims_bookings (collection_center_id, booked_at DESC)
            WHERE deleted_at IS NULL
        ');

        DB::statement('
            CREATE INDEX lims_bookings_patient_idx
            ON lims_bookings (patient_id, booked_at DESC)
            WHERE deleted_at IS NULL
        ');

        DB::statement('
            CREATE INDEX lims_bookings_status_idx
            ON lims_bookings (status)
            WHERE deleted_at IS NULL
        ');

        Schema::create('lims_booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('lims_bookings')->cascadeOnDelete();
            $table->foreignId('collection_center_id')->constrained('collection_centers');
            $table->foreignId('test_id')->constrained('tests');
            // No test_categories table yet — nullable bigint without FK
            $table->unsignedBigInteger('test_category_id')->nullable();
            $table->text('test_name_snapshot');
            $table->decimal('list_price', 12, 2);
            $table->decimal('net_price', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->text('status')->default('pending');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['booking_id', 'test_id'], 'lims_booking_items_booking_test_uq');
            $table->index('collection_center_id', 'lims_booking_items_cc_idx');
        });

        DB::statement('ALTER TABLE lims_booking_items ADD COLUMN sample_status sample_status NOT NULL DEFAULT \'booked\'');
    }

    public function down(): void
    {
        Schema::dropIfExists('lims_booking_items');
        Schema::dropIfExists('lims_bookings');
        Schema::dropIfExists('lab_number_sequences');

        DB::statement('DROP TYPE IF EXISTS sample_status');
        DB::statement('DROP TYPE IF EXISTS booking_status');
    }
};
