<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations');
            $table->text('code');
            $table->text('name');
            $table->text('lab_number_prefix');
            $table->text('address')->nullable();
            $table->text('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['organization_id', 'code'], 'collection_centers_org_code_uq');
            $table->unique(['organization_id', 'lab_number_prefix'], 'collection_centers_org_prefix_uq');
        });

        DB::statement('ALTER TABLE collection_centers ADD COLUMN kind org_site_kind NOT NULL');

        DB::statement('
            CREATE UNIQUE INDEX collection_centers_one_main_lab_uq
            ON collection_centers (organization_id)
            WHERE kind = \'main_lab\' AND deleted_at IS NULL
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_centers');
    }
};
