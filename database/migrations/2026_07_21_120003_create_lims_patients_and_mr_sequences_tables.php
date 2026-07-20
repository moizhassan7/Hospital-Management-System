<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Named lims_patients: HMS already has a create_patients_table migration
        // (indoor/outdoor registration) with an incompatible schema. Additive only.
        Schema::create('lims_patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations');
            $table->text('mr_no');
            $table->text('full_name');
            $table->text('gender');
            $table->date('date_of_birth')->nullable();
            $table->smallInteger('age_years')->nullable();
            $table->text('contact_no')->nullable();
            $table->text('cnic')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('created_at_cc_id')
                ->nullable()
                ->constrained('collection_centers');
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['organization_id', 'mr_no'], 'patients_org_mr_uq');
        });

        DB::statement("
            ALTER TABLE lims_patients
            ADD CONSTRAINT lims_patients_gender_chk
            CHECK (gender IN ('Male', 'Female', 'Other'))
        ");

        DB::statement('
            CREATE INDEX patients_contact_idx
            ON lims_patients (organization_id, contact_no)
            WHERE contact_no IS NOT NULL AND deleted_at IS NULL
        ');

        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        DB::statement('
            CREATE INDEX patients_name_trgm_idx
            ON lims_patients USING gin (full_name gin_trgm_ops)
        ');

        Schema::create('mr_number_sequences', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->primary()
                ->constrained('organizations');
            $table->bigInteger('next_value')->default(1);
            $table->timestampTz('updated_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mr_number_sequences');
        Schema::dropIfExists('lims_patients');
    }
};
