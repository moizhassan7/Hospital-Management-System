<?php

// database/migrations/xxxx_xx_xx_add_status_to_laboratory_patients_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('laboratory_patients', function (Blueprint $table) {
            $table->string('status')->default('Pending')->after('due_amount');
        });
    }

    public function down()
    {
        Schema::table('laboratory_patients', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};