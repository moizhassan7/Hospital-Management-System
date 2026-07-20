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
            CREATE TYPE org_site_kind AS ENUM ('main_lab', 'collection_center');
        EXCEPTION WHEN duplicate_object THEN NULL;
        END $$;");

        DB::statement("DO $$ BEGIN
            CREATE TYPE user_scope AS ENUM ('main_lab', 'collection_center');
        EXCEPTION WHEN duplicate_object THEN NULL;
        END $$;");

        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->text('code')->unique();
            $table->text('name');
            $table->text('timezone')->default('Asia/Karachi');
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');

        DB::statement('DROP TYPE IF EXISTS user_scope');
        DB::statement('DROP TYPE IF EXISTS org_site_kind');
    }
};
