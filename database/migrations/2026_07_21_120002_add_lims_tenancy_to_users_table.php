<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('branch')
                ->constrained('organizations');
            $table->foreignId('collection_center_id')
                ->nullable()
                ->after('organization_id')
                ->constrained('collection_centers');
            $table->softDeletesTz();
        });

        // Existing users have no CC assignment; default to main_lab so the
        // scope/CC check constraint is satisfied (architecture default of
        // collection_center would violate CHECK for null collection_center_id).
        DB::statement("ALTER TABLE users ADD COLUMN user_scope user_scope NOT NULL DEFAULT 'main_lab'");

        DB::statement('
            CREATE INDEX users_cc_idx
            ON users (collection_center_id)
            WHERE deleted_at IS NULL
        ');

        DB::statement("
            ALTER TABLE users
            ADD CONSTRAINT users_scope_cc_chk CHECK (
                (user_scope = 'main_lab' AND collection_center_id IS NULL)
                OR (user_scope = 'collection_center' AND collection_center_id IS NOT NULL)
                OR (user_scope = 'collection_center' AND deleted_at IS NOT NULL)
            )
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_scope_cc_chk');
        DB::statement('DROP INDEX IF EXISTS users_cc_idx');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('collection_center_id');
            $table->dropConstrainedForeignId('organization_id');
            $table->dropSoftDeletes();
        });

        DB::statement('ALTER TABLE users DROP COLUMN IF EXISTS user_scope');
    }
};
