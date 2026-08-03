<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_scope_cc_chk');
            
            \Illuminate\Support\Facades\DB::statement("
                ALTER TABLE users
                ADD CONSTRAINT users_scope_cc_chk CHECK (
                    (user_scope = 'main_lab')
                    OR (user_scope = 'collection_center' AND collection_center_id IS NOT NULL)
                    OR (user_scope = 'collection_center' AND deleted_at IS NOT NULL)
                )
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_scope_cc_chk');
            
            \Illuminate\Support\Facades\DB::statement("
                ALTER TABLE users
                ADD CONSTRAINT users_scope_cc_chk CHECK (
                    (user_scope = 'main_lab' AND collection_center_id IS NULL)
                    OR (user_scope = 'collection_center' AND collection_center_id IS NOT NULL)
                    OR (user_scope = 'collection_center' AND deleted_at IS NOT NULL)
                )
            ");
        }
    }
};
