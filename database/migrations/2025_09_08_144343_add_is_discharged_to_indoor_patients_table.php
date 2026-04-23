<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('indoor_patients', function (Blueprint $table) {
        $table->boolean('is_discharged')->default(false)->after('consultant_id');
        $table->date('discharge_date')->nullable()->after('is_discharged');
        $table->time('discharge_time')->nullable()->after('discharge_date');
        $table->string('discharge_status')->nullable()->after('discharge_time');
    });
}

public function down()
{
    Schema::table('indoor_patients', function (Blueprint $table) {
        $table->dropColumn(['is_discharged', 'discharge_date', 'discharge_time', 'discharge_status']);
    });
}
};
