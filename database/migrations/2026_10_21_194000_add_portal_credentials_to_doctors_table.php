<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('name');
            $table->string('password')->nullable()->after('email');
        });

        $demo = DB::table('doctors')->where('code', 'DOC001')->whereNull('email')->first();
        if ($demo) {
            DB::table('doctors')->where('id', $demo->id)->update([
                'email' => 'doctor@example.com',
                'password' => Hash::make('password'),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropColumn(['email', 'password']);
        });
    }
};
