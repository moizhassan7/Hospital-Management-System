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
            CREATE TYPE invoice_status AS ENUM ('open', 'paid', 'partial', 'void', 'refunded');
        EXCEPTION WHEN duplicate_object THEN NULL;
        END $$;");

        DB::statement("DO $$ BEGIN
            CREATE TYPE payment_method AS ENUM ('cash', 'card', 'bank', 'online', 'adjustment');
        EXCEPTION WHEN duplicate_object THEN NULL;
        END $$;");

        Schema::create('lims_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations');
            $table->foreignId('collection_center_id')->constrained('collection_centers');
            $table->foreignId('booking_id')->unique()->constrained('lims_bookings');
            $table->text('invoice_no');
            $table->decimal('sub_total', 12, 2);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);
            $table->decimal('paid_total', 12, 2)->default(0);
            $table->decimal('due_total', 12, 2);
            $table->timestampTz('invoiced_at')->useCurrent();
            $table->timestampTz('voided_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['organization_id', 'invoice_no'], 'lims_invoices_org_no_uq');
        });

        DB::statement('ALTER TABLE lims_invoices ADD COLUMN status invoice_status NOT NULL DEFAULT \'open\'');

        Schema::create('lims_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations');
            $table->foreignId('collection_center_id')->constrained('collection_centers');
            $table->foreignId('invoice_id')->constrained('lims_invoices');
            $table->decimal('amount', 12, 2);
            $table->timestampTz('paid_at')->useCurrent();
            $table->foreignId('received_by')->nullable()->constrained('users');
            // cash_closures FK added in P4
            $table->unsignedBigInteger('cash_closure_id')->nullable();
            $table->text('idempotency_key')->nullable();
            $table->text('notes')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->softDeletesTz();

            $table->unique(
                ['collection_center_id', 'idempotency_key'],
                'lims_payments_cc_idem_uq'
            );
        });

        DB::statement('ALTER TABLE lims_payments ADD COLUMN method payment_method NOT NULL');

        DB::statement('
            ALTER TABLE lims_payments
            ADD CONSTRAINT lims_payments_amount_chk CHECK (amount > 0)
        ');

        DB::statement('
            CREATE INDEX lims_payments_cc_paid_idx
            ON lims_payments (collection_center_id, paid_at)
            WHERE deleted_at IS NULL
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('lims_payments');
        Schema::dropIfExists('lims_invoices');

        DB::statement('DROP TYPE IF EXISTS payment_method');
        DB::statement('DROP TYPE IF EXISTS invoice_status');
    }
};
