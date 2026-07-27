<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP INDEX IF EXISTS collection_centers_one_main_lab_uq');
    }

    public function down(): void
    {
        DB::statement('
            CREATE UNIQUE INDEX collection_centers_one_main_lab_uq
            ON collection_centers (organization_id)
            WHERE kind = \'main_lab\' AND deleted_at IS NULL
        ');
    }
};
