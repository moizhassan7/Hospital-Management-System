<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laboratory_patients', function (Blueprint $table) {
            $table->string('discount_type')->nullable()->default('flat')->after('discount');
            $table->decimal('discount_value', 12, 2)->nullable()->default(0)->after('discount_type');
        });
    }

    public function down(): void
    {
        Schema::table('laboratory_patients', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value']);
        });
    }
};
